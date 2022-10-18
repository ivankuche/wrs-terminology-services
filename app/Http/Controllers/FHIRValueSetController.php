<?php

namespace App\Http\Controllers;

use App\Models\FHIRValueSet;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use GuzzleHttp\Client;
use ReflectionMethod;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use ReflectionClass;

class FHIRValueSetController extends Controller
{

    private function valuesetMapper($collection)
    {
        $return= null;

        foreach ($collection as $item)
            $return[$item["name"]]= $item["url"];

        return $return;
    }

    /*
        Start special cases
    */
    private function specialCaseBirthSex(&$options,$Term,&$urlsMeta) {
        $this->addCustomValues($options,
            "https://terminology.hl7.org/3.1.0/CodeSystem-v3-NullFlavor.json",
            ["UNK","ASKU"],
            $Term,
            $urlsMeta);
    }

    private function specialCaseCarePlanActivityKind(&$options,$Term,&$urlsMeta) {
        $this->addCustomValues($options,
            "https://www.hl7.org/fhir/codesystem-resource-types.json",
            ["Appointment","CommunicationRequest","DeviceRequest","MedicationRequest","NutritionOrder",
                "Task","ServiceRequest","VisionPrescription"],
            $Term,
            $urlsMeta);
    }

    private function specialCaseCarePlanActivityStatusReason(&$options,$Term,&$urlsMeta) {
        $this->addCustomValues($options,
            "https://terminology.hl7.org/3.1.0/CodeSystem-v3-ActReason.json",
            ["IMMUNE","MEDPREC","OSTOCK","PATOBJ","PHILISOP","RELIG","VACEFF","VACSAF"],
            $Term,
            $urlsMeta);
    }

    private function specialCaseParticipationRoleType(&$options,$Term,&$urlsMeta)
    {

        // NEMA
        $url="ftp://medical.nema.org/medical/dicom/resources/valuesets/fhir/json/ValueSet-dicom-cid-402-AuditActiveParticipantRoleIDCode.json";

        $content= file_get_contents($url);
        $data= json_decode(str_replace("\n","",$content))->compose->include[0];
        $data->id= "ParticipationRoleType-NEMA";


        $this->addCustomValues($options,
        "",
        ["110150","110151","110152","110153","110154","110155"],
        $Term,
        $urlsMeta,$data);
    }


    private function specialCaseUSCoreConditionCategoryCodes(&$options,$Term,&$urlsMeta) {
        $this->addCustomValues($options,
            "http://hl7.org/fhir/us/core/STU5/CodeSystem-condition-category.json",
            ["health-concern"],
            $Term,
            $urlsMeta);
    }

    /*
        End special cases
    */

    private function specialCases($ValueSet,$Term,&$options,&$urlsMeta)
    {
            // Special cases where extra info must be placed. All cases must be in lower case.
            switch ($ValueSet)
            {

                case "birthsex":
                    $this->specialCaseBirthSex($options,$Term,$urlsMeta);
                    break;

                case "careplanactivitykind":
                    $this->specialCaseCarePlanActivityKind($options,$Term,$urlsMeta);
                    break;

                case "careplanactivitystatusreason":
                    $this->specialCaseCarePlanActivityStatusReason($options,$Term,$urlsMeta);
                    break;

                case "participationroletype":
                    $this->specialCaseParticipationRoleType($options,$Term,$urlsMeta);
                    break;

                case "uscoreconditioncategorycodes":
                    $this->specialCaseUSCoreConditionCategoryCodes($options,$Term,$urlsMeta);
                    break;

            }

    }

    public function getResults($ValueSet,$Term=null,$Sort=null)
    {
        $dbValueSets= new FHIRValueSet();
        $ValueSet= strtolower($ValueSet); // ValueSet name is set to lower case
        $valuesets= array_change_key_case($this->valuesetMapper($dbValueSets->all()->toArray())); // List of ValueSets is set to lower case


        if (array_key_exists(strtolower($ValueSet),$valuesets))
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

            $this->specialCases($ValueSet,$Term,$options,$urlsMeta);

            if ($Sort!=null)
                $options= collect($options)->sortBy($Sort,SORT_STRING)->values();


            unset($body->concept,$body->text);
            return [
                "meta"=>$urlsMeta,
                "data"=>$options
            ];
        }
        else
        {
            // Custom methods
            if (method_exists($this,$ValueSet))
            {
                return $this->{$ValueSet}();
            }
        }

        throw new NotFoundHttpException("ValueSet not found");
    }


    private final function ImmunizationStatusReasonCodes ()
    {
        $meta= [];
        $data= [];

        $this->addCustomValues($data,
            "https://terminology.hl7.org/4.0.0/CodeSystem-v3-ActReason.json",
            ["IMMUNE","OSTOCK","MEDPREC","PATOBJ", "PHILISOP","RELIG","VACEFF","VACSAF"],
            null,
            $meta);

        return [
            "meta"=>$meta,
            "data"=>$data,
        ];
    }

    private final function ImmunizationStatusCodes()
    {
        $meta= [];
        $data= [];

        $this->addCustomValues($data,
            "http://hl7.org/fhir/R4B/codesystem-event-status.json",
            ["entered-in-error","not-done"],
            null,
            $meta);

        return [
            "meta"=>$meta,
            "data"=>$data,
        ];

    }

    private final function ImmunizationOriginCodes()
    {
        $meta= [];
        $data= [];

        $this->addCustomValues($data,
            "https://terminology.hl7.org/4.0.0/CodeSystem-immunization-origin.json",
            ["provider","record","recall","school"],
            null,
            $meta);

        return [
            "meta"=>$meta,
            "data"=>$data,
        ];
    }

    private final function USCoreObservationSmokingStatusStatus()
    {
        $meta= [];
        $data= [];

        $this->addCustomValues($data,
            "http://hl7.org/fhir/R4/codesystem-observation-status.json",
            ["final","entered-in-error"],
            null,
            $meta);
        return [
            "meta"=>$meta,
            "data"=>$data,
        ];
    }

    private final function SpecialCourtesy()
    {

        $meta= [];
        $data= [];

        $this->addCustomValues($data,
            "https://terminology.hl7.org/4.0.0/CodeSystem-v3-EncounterSpecialCourtesy.json",
            ["EXT","NRM", "PRF", "STF", "VIP"],
//            ["final","entered-in-error"],
            null,
            $meta);

        $this->addCustomValues($data,
            "https://terminology.hl7.org/3.1.0/CodeSystem-v3-NullFlavor.json",
            ["UNK"],
            null,
            $meta);




        return [
            "meta"=>$meta,
            "data"=>$data,
        ];
    }


    private final function ImmunizationRouteCodes()
    {
        $meta= [];
        $data= [];

        $this->addCustomValues($data,
            "https://terminology.hl7.org/4.0.0/CodeSystem-v3-RouteOfAdministration.json",
            ["IDINJ","IM","IVINJ","PO","SQ","TRNSDERM"],
            null,
            $meta);
        return [
            "meta"=>$meta,
            "data"=>$data,
        ];
    }

    private final function ImmunizationFunctionCodes()
    {
        $meta= [];
        $data= [];

        $this->addCustomValues($data,
            "https://terminology.hl7.org/4.0.0/CodeSystem-v2-0443.json",
            ["AP","OP"],
            null,
            $meta);
        return [
            "meta"=>$meta,
            "data"=>$data,
        ];
    }


    private final function MimeType() {
        $mimeTypes= [];
        // Retrieved from IANA
        $sources= ["https://www.iana.org/assignments/media-types/application.csv",
            "https://www.iana.org/assignments/media-types/audio.csv",
            "https://www.iana.org/assignments/media-types/font.csv",
            "https://www.iana.org/assignments/media-types/image.csv",
            "https://www.iana.org/assignments/media-types/message.csv",
            "https://www.iana.org/assignments/media-types/model.csv",
            "https://www.iana.org/assignments/media-types/multipart.csv",
            "https://www.iana.org/assignments/media-types/text.csv",
            "https://www.iana.org/assignments/media-types/video.csv"];

        $client = new Client();

        foreach ($sources as $source)
        {

            $response = $client->get($source);

            if ($response->getStatusCode()==200)
            {
                $lineNumber=1;
                $separator = "\r\n";
                $line = strtok($response->getBody()->getContents(), $separator);

                while ($line !== false) {
                    // Ignore first 2 lines (headers and empty line)
                    if ($lineNumber>=3)
                    {

                        $data= explode(",",$line);
                        $mimeTypes[]= [
                            "definition"=>$data[1],
                            "value"=>$data[0],
                            "name"=>$data[1],
                            "source"=>"iana-mimetypes"
                        ];
                    }
                    # do something with $line
                    $line = strtok( $separator );
                    $lineNumber++;

                }

            }
            else
                throw new InternalErrorException($response->getStatusCode());

        }

        $meta["iana-mimetypes"]= [];

        return ["meta"=>$meta,
                "data"=>$mimeTypes];

    }

    private final function CommonLanguages() {

        //
        $commonLanguages= [];
        $meta= "";

        $client = new Client();
        // Retrieved from IANA
        $response = $client->get("https://www.iana.org/assignments/language-subtag-registry/language-subtag-registry");


        if ($response->getStatusCode()==200)
        {
            $content= $response->getBody()->getContents();
            $meta= [];

            $tmpData= explode(":",str_replace("\n","",substr($content,0,strpos($content,"\n")+1)));
            $meta["common_languages"]= [$tmpData[0]=>$tmpData[1]];

            // Remove first line
            $content= substr($content,strpos($content,"\n")+1);
            $commonLanguages= [];

            $items= explode("%%\n",$content);
            foreach ($items as $item)
            {
                if ($item!="")
                {
                    $arrayItems= explode("\n",$item);
                    $tmpArray= [];
                    foreach ($arrayItems as $value)
                    {
                        if (trim($value)!="")
                        {
                            $tmpValue= explode(":",$value);

                            if (count($tmpValue)<2)
                                $tmpArray[array_keys($tmpArray)[count($tmpArray)-1]].= $value;
                            else
                                $tmpArray[$tmpValue[0]]= trim($tmpValue[1]);
                        }
                    }

                    $tmpArray["source"]="common_languages";

                    $commonLanguages[]= $tmpArray;

                }
            }

        }
        else
            throw new InternalErrorException($response->getStatusCode());



        return ["meta"=>$meta,
                "data"=>$commonLanguages];
    }

    private final function CodesForImmunizationSiteOfAdministration () {
        $meta= [];
        $data= [];

        $this->addCustomValues($data,
            "https://terminology.hl7.org/4.0.0/CodeSystem-v3-ActSite.json",
            ["LA","RA"],
            null,
            $meta);

        return [
            "meta"=>$meta,
            "data"=>$data,
        ];

    }


    private final function OmbRaceCategories($Term=null) {

        $raceCategories= [];
        $meta= [];

        // Get info from UMLS
        $APIkey= "3bcb695f-b6ef-4291-9939-3b8a7c7c869c"; //Replace with WRS corresponding one

        $client = new Client();

        $urls= ["https://cts.nlm.nih.gov/fhir/ValueSet/2.16.840.1.114222.4.11.836"];

        foreach ($urls as $url)
        {
            $response = $client->get($url, ["auth"=>['apikey',$APIkey]]);

            if ($response->getStatusCode()==200)
            {
                $data= json_decode($response->getBody()->getContents());
                $meta[$url]= [
                    "url"=> $data->url,
                    "version"=>$data->version,
                    "date"=>$data->date
                ];

                foreach ($data->compose->include as $include)
                {
                    foreach ($include->concept as $concept)
                    {
                        $raceCategories[]= [
                            "definition" => $concept->display,
                            "value" => $concept->code,
                            "name" => $concept->display,
                            "source" => $url
                        ];
                    }
                }

           }
        }


        $this->addCustomValues($raceCategories,
            "https://terminology.hl7.org/3.1.0/CodeSystem-v3-NullFlavor.json",
            ["UNK","ASKU"],
            $Term,
            $meta);

        return ["meta"=>$meta,
            "data"=>$raceCategories];
    }

    private final function CarePlanActivityKind($Term=null)
    {
        $activitiesPlan= [];
        $meta= [];

        $client = new Client();

        $urls= ["http://hl7.org/fhir/R4/valueset-care-plan-activity-kind.json"];

        foreach ($urls as $url)
        {
            $response = $client->get($url);

            if ($response->getStatusCode()==200)
            {
                $data= json_decode($response->getBody()->getContents());
                $meta[$url]= [
                    "url"=> $data->url,
                    "version"=>$data->version,
                    "date"=>$data->date
                ];

                foreach ($data->compose->include as $include)
                {
                    foreach ($include->concept as $concept)
                    {
                        $activitiesPlan[]= [
                            "definition" => (isset($concept->display)?$concept->display:$concept->code),
                            "value" => $concept->code,
                            "name" => (isset($concept->display)?$concept->display:$concept->code),
                            "source" => $url
                        ];
                    }
                }

           }
        }


        return ["meta"=>$meta,
            "data"=>$activitiesPlan];

    }

    private final function USCoreDocumentReferenceType()
    {
        return $this->LOINCRetrieveAll(["SCALE_TYP"=>"DOC"]);
    }

    private final function USCoreDiagnosticReportLabCodes()
    {
        $return= $this->LOINCRetrieveAll(["CLASSTYPE"=>"1"]);
        $this->addCustomValues($return["data"],
            "https://terminology.hl7.org/3.1.0/CodeSystem-v3-NullFlavor.json",
            ["UNK","ASKU"],
            null,
            $return["meta"]);

        return $return;
    }

    private function LOINCRetrieveAll($filter)
    {
        $dataReturned= [];
        $meta= [];

        $this->getLOINCmeta($meta);

        if ($meta!=[])
        {
            $tmpFilter= [];
            foreach ($filter as $key=>$value)
                $tmpFilter[]= "_".$key."=".$value;

            $client = new Client();


            $loincAPIUrlMeta= "https://fhir.loinc.org/ValueSet?".implode("&",$tmpFilter);


            $response = $client->get($loincAPIUrlMeta,["auth"=>[env("USER_LOINC"),env("PASS_LOINC")],"header"=>["Accept"=>"application/json"]]);

            if ($response->getStatusCode()==200)
            {
                $data= json_decode($response->getBody()->getContents());

                $resultset= [];
                foreach ($data->entry as $entry)
                {
                    foreach ($entry->resource->compose->include as $include)
                    {
                        foreach ($include->concept as $concept)
                        {
                            $url= array_values($meta)[0]["url"];
                            $dataReturned[]= [
                                "definition" => $concept->display,
                                "value" => $concept->code,
                                "name" => $concept->display,
                                "source" => $url
                            ];

                        }
                    }
                }
            }
        }

        return ["meta"=>$meta,
            "data"=>$dataReturned];

    }

    private function getLOINCmeta(&$meta)
    {
        $client = new Client();
        $loincAPIUrlMeta= "https://fhir.loinc.org/CodeSystem/?url=http://loinc.org";
        $response = $client->get($loincAPIUrlMeta,["auth"=>[env("USER_LOINC"),env("PASS_LOINC")],"header"=>["Accept"=>"application/json"]]);

        if ($response->getStatusCode()==200)
        {
            $data= json_decode($response->getBody()->getContents());


            $meta[$data->entry[1]->fullUrl]= [
                "url"=> $data->entry[1]->fullUrl,
                "version"=>$data->entry[1]->resource->version,
                "date"=>$data->entry[1]->resource->meta->lastUpdated
            ];
        }

    }

    private function LoincBasicAPISearch($codes)
    {
        $dataReturned= [];
        $meta= [];

        $this->getLOINCmeta($meta);

        if ($meta!=[])
        {
            $client = new Client();
            $loincAPIUrl= "https://fhir.loinc.org/CodeSystem/\$lookup?system=http://loinc.org&code=";

            foreach ($codes as $code)
            {
                // WRS must have it's own LOINC registered user
                $response = $client->get($loincAPIUrl.$code,["auth"=>[env("USER_LOINC"),env("PASS_LOINC")]]);

                if ($response->getStatusCode()==200)
                {
                    $data= json_decode($response->getBody()->getContents());

                    $resultset= [];
                    foreach ($data->parameter as $parameter)
                    {
                        if (isset($parameter->valueString))
                            $resultset[$parameter->name]= $parameter->valueString;
                    }

                    $dataReturned[]= [
                        "definition" => $resultset["display"],
                        "value" => $resultset["display"],
                        "name" => $resultset["display"],
                        "source" => array_keys($meta)[0]

                    ];
               }
            }
        }
        else
            throw new Exception("Problem with LOINC");

        return ["meta"=>$meta,
            "data"=>$dataReturned];

    }

    private final function USCoreDiagnosticReportCategory($Term=null)
    {
        return $this->LoincBasicAPISearch(["LP29684-5","LP29708-2","LP7839-6"]);
    }

    private final function CareTeamCategory($Term=null)
    {
        return $this->LoincBasicAPISearch(["LA27975-4","LA27976-2","LA27977-0","LA27978-8","LA28865-6","LA28866-4","LA27980-4","LA28867-2"]);
    }


    // PENDING
    private final function DetailedRace($Term=null) // PENDING
    {
        $raceCategories= [];
        $meta= [];

        // Get info from UMLS
        $APIkey= "3bcb695f-b6ef-4291-9939-3b8a7c7c869c"; //Replace with WRS corresponding one

        $client = new Client();

        $urls= ["http://cts.nlm.nih.gov/fhir/ValueSet/2.16.840.1.113883.1.11.14914"];


        /*
        Import all the codes that are contained in http://cts.nlm.nih.gov/fhir/ValueSet/2.16.840.1.113883.1.11.14914
        Import all the codes that are contained in http://cts.nlm.nih.gov/fhir/ValueSet/2.16.840.1.113762.1.4.1021.103
        This value set excludes codes based on the following rules:

        Import all the codes that are contained in http://cts.nlm.nih.gov/fhir/ValueSet/2.16.840.1.113883.3.2074.1.1.3
        */

        foreach ($urls as $url)
        {
            $response = $client->get($url, ["auth"=>['apikey',$APIkey]]);

            if ($response->getStatusCode()==200)
            {
                $data= json_decode($response->getBody()->getContents());
                dd($data);
                $meta[$url]= [
                    "url"=> $data->url,
                    "version"=>$data->version,
                    "date"=>$data->date
                ];

                foreach ($data->compose->include as $include)
                {
                    foreach ($include->concept as $concept)
                    {
                        $raceCategories[]= [
                            "definition" => $concept->display,
                            "value" => $concept->code,
                            "name" => $concept->display,
                            "source" => $url
                        ];
                    }
                }

            }
        }


        $this->addCustomValues($raceCategories,
            "https://terminology.hl7.org/3.1.0/CodeSystem-v3-NullFlavor.json",
            ["UNK","ASKU"],
            $Term,
            $meta);

        return dd(["meta"=>$meta,
            "data"=>$raceCategories]);

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

    private function addCustomValues(&$SourceValues,$url,$customValues,$Term,&$Meta,$providedJSON=null)
    {

        if ($providedJSON==null)
        {
            $client = new Client();
            $response = $client->get($url);

            if ($response->getStatusCode()==200)
            {
                // Lower case of the Custom Values
                $body= json_decode($response->getBody());
                $concepts= $body->concept;
                unset($body->concept,$body->text);
            }
            else
                throw new InternalErrorException($response->getStatusCode());

        }
        else{
            $body= $providedJSON;
            $concepts= $providedJSON->concept;
            unset($providedJSON->concept,$providedJSON->text);
        }


        $search_array = array_map('strtolower', $customValues);

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

    public function getMethods()
    {
        $response= [];

        $class = new ReflectionClass("App\\Http\\Controllers\\FHIRValueSetController");
        $methods = $class->getMethods(ReflectionMethod::IS_FINAL);


        // Check final methods that this class has, as they are the special cases outside the db
        foreach ($methods as $method)
            $response[]= $method->name;

        $valueSets= new FHIRValueSet();
        foreach ($valueSets->all(["name"])->toArray() as $valueset)
            $response[]= $valueset["name"];

        return $response;
    }

}
