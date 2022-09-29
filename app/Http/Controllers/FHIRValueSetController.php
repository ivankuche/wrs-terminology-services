<?php

namespace App\Http\Controllers;

use App\Models\FHIRValueSet;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use GuzzleHttp\Client;
use Symfony\Component\CssSelector\Exception\InternalErrorException;

class FHIRValueSetController extends Controller
{

    private function valuesetMapper($collection)
    {
        $return= null;

        foreach ($collection as $item)
            $return[$item["name"]]= $item["url"];

        return $return;
    }

    public function getValueSet($ValueSet,$Term=null,$Sort=null)
    {
        $dbValueSets= new FHIRValueSet();
        $valuesets= $this->valuesetMapper($dbValueSets->all()->toArray());

        if (array_key_exists($ValueSet,$valuesets))
        {
            $client = new Client();
            $options= [];
            $urls= $valuesets[$ValueSet];
            $urlsMeta= [];

            if (!is_array($urls))
            {
                $tmp= $urls;
                unset($urls);
                $urls[]= $tmp;
            }

            foreach ($urls as $url)
            {

                if ($url!=null)
                {
                    $response = $client->get($url);

                    if ($response->getStatusCode()==200)
                    {
                        $body= json_decode($response->getBody());
                        $concepts= $body->concept;
                        unset($body->concept,$body->text);
                        $urlsMeta[$body->id]= $body;

                        $this->recursiveFilling($concepts,$body->id,$options,$Term);

                    }
                    else
                        throw new InternalErrorException($response->getStatusCode());
                }


            }

            // Special cases where extra info must be placed
            switch ($ValueSet)
            {

                case "BirthSex":

                    $this->addCustomValues($options,
                        "https://terminology.hl7.org/3.1.0/CodeSystem-v3-NullFlavor.json",
                        ["UNK"],
                        $Term,
                        $urlsMeta);

                    break;

                case "CarePlanActivityKind":
                    $this->addCustomValues($options,
                        "https://www.hl7.org/fhir/codesystem-resource-types.json",
                        ["Appointment","CommunicationRequest","DeviceRequest","MedicationRequest","NutritionOrder",
                            "Task","ServiceRequest","VisionPrescription"],
                        $Term,
                        $urlsMeta);

                    break;

                case "CarePlanActivityStatusReason":
                    $this->addCustomValues($options,
                        "https://terminology.hl7.org/3.1.0/CodeSystem-v3-ActReason.json",
                        ["IMMUNE","MEDPREC","OSTOCK","PATOBJ","PHILISOP","RELIG","VACEFF","VACSAF"],
                        $Term,
                        $urlsMeta);


                    break;
            }

            if ($Sort!=null)
                $options= collect($options)->sortBy($Sort,SORT_STRING)->values();


            unset($body->concept,$body->text);
            return [
                "meta"=>$urlsMeta,
                "data"=>$options
            ];
        }

        throw new NotFoundHttpException("ValueSet not found");
    }

    private function recursiveFilling($Source,$SourceID,&$Destination,$Term)
    {
        foreach ($Source as $concept)
        {
            // Is no defition is set we use Display or Code as default
            $definition= $concept->code;

            if (isset($concept->display))
                $definition=$concept->display;

            if (isset($concept->definition))
                $definition= $concept->definition;

            $insert= true;

            if (($Term!=null) && ($Term!="*"))
            {
                if (strpos(strtolower($concept->code),strtolower($Term))===false)
                    $insert= false;
            }

            $newElement= [
                "definition"=>$definition,
                "value"=>$concept->code,
                "name"=>(isset($concept->display)==true) ? $concept->display : $concept->code,
                "source"=>$SourceID,
            ];

            if (isset($concept->concept))
                $this->recursiveFilling($concept->concept,$SourceID,$newElement["children"],$Term);

            if ($insert)
                $Destination[] = $newElement;

        }

    }

    private function findRecursiveElement($Source,$key,$value,$SourceID)
    {
        foreach ($Source as $element)
        {
            if ((isset($element->$key)) && ($element->$key==$value))
            {
                $definition= $element->display;
                if (isset($element->definition))
                    $definition= $element->definition;

                return [
                    "definition"=>$definition,
                    "value"=>$element->code,
                    "name"=>$element->display,
                    "source"=>$SourceID
                ];
            }
            else{
                if (isset($element->concept))
                    return $this->findRecursiveElement($element->concept,$key,$value,$SourceID);
            }
        }

    }

    private function addCustomValues(&$SourceValues,$url,$customValues,$Term,&$Meta)
    {

        $client = new Client();
        $response = $client->get($url);

        if ($response->getStatusCode()==200)
        {
            // Lower case of the Custom Values
            $search_array = array_map('strtolower', $customValues);
            $body= json_decode($response->getBody());


            $body= json_decode($response->getBody());
            $concepts= $body->concept;
            unset($body->concept,$body->text);

            foreach ($customValues as $customValue)
            {
                $concept= $this->findRecursiveElement($concepts,"code",$customValue,$body->id);

                if ($concept!=[])
                {
                    $insert= true;

                    if (($Term!=null) && ($Term!="*"))
                    {
                        if (strpos(strtolower($concept["value"]),strtolower($Term))===false)
                            $insert= false;
                    }

                    if ($insert)
                    {
                        $SourceValues[] = $concept;
                        $Meta[$body->id]= $body;
                    }
                }
            }
        }
        else
            throw new InternalErrorException($response->getStatusCode());

    }

    public function getMethods()
    {
        return array_keys($valuesets);
    }

}
