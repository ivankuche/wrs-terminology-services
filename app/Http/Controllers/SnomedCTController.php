<?php

namespace App\Http\Controllers;

use App\Models\SnomedCTECL;
use Exception;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\CssSelector\Exception\InternalErrorException;


class SnomedCTController extends Controller
{
    private function parseResponse($elements)
    {
        $response= [];

        foreach ($elements as $element)
        {
            try
            {
                $response[]= [
                    "value"=>$element->conceptId,
                    "definition"=>$element->fsn->term,
                    "name"=>$element->pt->term,
                    "source"=>"SnomedCTResponse"
                ];
            }
            catch (Exception $e)
            {
            }
        }

        return collect($response);
    }

    private function queryConcept($concept,$term=null,$sort=null)
    {
        $client = new Client();

        //$url= "https://snowstorm.msal.gov.ar/MAIN/concepts";

        // Future WRS endpoint
        $url= "https://snowstorm.fhirdev.wrs.cloud/MAIN/concepts";

        $params= [
            "query"=>[
                'activeFilter'=>true,
                'termActive'=>true,
                'language'=>'en',
                'ecl'=>$concept,
                'active'=>true,
                'limit'=>200
            ],
            "headers"=> [
                "Accept-Language"=>"en"
            ]
        ];

        if (($term!=null) && ($term!="*"))
            $params["query"]["term"]= $term;

        $response= $client->request('GET',$url,$params);

        if ($response->getStatusCode()==200)
        {
            $body= json_decode($response->getBody());

            $return= $this->parseResponse($body->items);

            if ($sort!=null)
                $return= $return->sortBy($sort,SORT_STRING)->values();

            return $return->toArray();
        }
        else
            throw new InternalErrorException($response->getStatusCode());

    }

    private function releaseStatus()
    {

        $client = new Client();

        //$url= "https://snowstorm.msal.gov.ar/branches/MAIN";

        // Future WRS endpoint
        $url= "https://snowstorm.fhirdev.wrs.cloud/branches/MAIN";


        $response= $client->request('GET',$url);

        if ($response->getStatusCode()==200)
        {
            $body= json_decode($response->getBody());

            return $body;
        }
        else
            throw new InternalErrorException($response->getStatusCode());
    }

    public function getResults($ConceptGroup,$Term=null,$Sort=null)
    {
        if (in_array($ConceptGroup,$this->getMethods()))
        {

            $return= [];
            $meta= [];
            $meta["SnomedCTResponse"]= $this->releaseStatus();
            $meta["SnomedCTResponse"]->resourceType= "SnomedCTResponse";

            $snomedECT= new SnomedCTECL();
            $ecl= $snomedECT->where(['name'=>$ConceptGroup])->get()->toArray();
            $return= $this->queryConcept($ecl[0]["ecl"],$Term,$Sort);

            return [
                "meta"=>$meta,
                "data"=>$return
            ];
        }

        throw new NotFoundHttpException("ValueSet not found");
    }

    public function getMethods()
    {
        $ecls= new SnomedCTECL();
        $names= [];

        foreach ($ecls->all("name")->toArray() as $key=>$value)
            $names[]= $value["name"];

        return $names;
    }

}
