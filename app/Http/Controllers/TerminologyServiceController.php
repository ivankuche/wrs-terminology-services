<?php

namespace App\Http\Controllers;

use App\Models\FHIRValueSet;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use GuzzleHttp\Client;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use App\Http\Controllers\SnomedCTController;
use App\Http\Controllers\FHIRValueSetController;

class TerminologyServiceController extends Controller
{


    // Possible terminlogies
    private $sources= [
        "FHIRValueSet",
        "SnomedCT",
    ];

    public function getMethods($ValueSet)
    {
        if (in_array($ValueSet,$this->sources))
        {


            // Replace next with API call such as CURL, made in a horrible fast way
            //$urlValueSet= url("/".$ValueSet."/".$Term);
            $instance = new ("App\\Http\\Controllers\\".$ValueSet."Controller");
            return $instance->getMethods();

            /*
            $client = new Client();

            $url= url($ValueSet)."/methods";

            // IMPORTANT: REMOVE VERIFY AS IT IS A WORKAROUND FOR AN SSL BLOCKER
            $response= $client->request('GET',$url,['verify' => false]);

            if ($response->getStatusCode()==200)
            {
                $body= json_decode($response->getBody());

                return $body;
            }
            else
                throw new InternalErrorException($response->getStatusCode());

            */

        }
        else
            throw new NotFoundHttpException("ValueSet/Terminology not found");
    }

    private function sourcesAvailable($Term)
    {
        $return= [];

        foreach ($this->sources as $source)
        {
            $methods= $this->getMethods($source);
            if (in_array($Term,$methods))
                $return[]= $source;
        }

        return $return;
    }

    private function copyInnerArray($source,&$destination,$move=false){
        foreach ($source as $key=>$value)
        {
            if ($move)
                $destination[]= $value;
            else
                $destination[$key]= $value;
        }
    }

    public function getValueSet($ValueSet,$Term=null,$Sort=null)
    {
        if (strtolower($ValueSet)=="all")
        {
            $data= [];
            $meta= [];
            foreach ($this->sourcesAvailable($Term) as $source)
            {
                $instance = new ("App\\Http\\Controllers\\".$source."Controller");
                $tmp= $instance->getResults($Term,$Sort);
                $this->copyInnerArray($tmp["meta"],$meta);
//                $data[]= $tmp["data"];
                $this->copyInnerArray($tmp["data"],$data,true);
            };
            return dd([
                "meta"=>$meta,
                "data"=>$data
            ]);
        }
        else
        {

            $methods= $this->getMethods($ValueSet);
            if (in_array($Term,$methods))
            {
                // Replace next with API call such as CURL, made in a horrible fast way
                $instance = new ("App\\Http\\Controllers\\".$ValueSet."Controller");
                return $instance->getResults($Term,$Sort);
            }
        }

        throw new NotFoundHttpException("ValueSet not found");
    }
}
