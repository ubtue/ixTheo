<?php
/**
 * Proxy Controller Module
 *
 * PHP version 5
 *
 * Copyright (C) Universitätsbiblothek Tübingen 2017.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category TueLib
 * @author   Johannes Riedl <johannes.riedl@uni-tuebingen.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 */
namespace TueLib\Controller;

use VuFind\Exception\Forbidden as ForbiddenException;
use \Exception as Exception;
/**
 * This controller is a proxy for requests to BSZ based GVI 
 * (= Gemeinsame Verbünde Index) to determine whether a monograph
 * can be obtained by interlibrary loan
 * @package  Controller
 */

class PDAProxyController extends \VuFind\Controller\AbstractBase
{

    protected $base_url = 'http://gvi.bsz-bw.de/solr/GVI/select';
    protected $base_query = 'rows=10&wt=json&facet=true&facet.field=ill_region&facet.field=ill_flag&q=(ill_region:BSZ+OR+ill_region:KOBV+OR+ill_region:BVB+OR+ill_region:GBV+OR+ill_region:HEBIS+OR+ill_region:DNB)+AND+isbn:';
    

    protected function isAvailableForILL($isbn)
    {
        $client = $this->getServiceLocator()->get('VuFind\Http')->createClient();
        $client->setUri($this->base_url . '?' . $this->base_query . $isbn);
        $response = $client->send();


        // Abort, if we have an HTTP Error
        if (! $response->isSuccess()){
            throw new Exception("HTTP ERROR"); 
        }

        // Abort, if general JSON decoding fails
        $json = json_decode($response->getBody(), true);
        if ($json == null) 
           throw new Exception("JSON PARSE ERROR"); 

        // We use a several way scheme
        // 1.) If we have a match with ILL -> OK
        // 2.) If can determine an ILL region take it as heuristics
        // 3.) Parse the fullrecord

        // Abort if we cannot find the appropriate fields
        if ((! isset($json['facet_counts']['facet_fields']['ill_flag'])) || (! isset($json['facet_counts']['facet_fields']['ill_region']))) {
           throw new Exception("JSON FACET FIELDs Missing"); 
        }

        // Case 1
        $ill_facet = $json['facet_counts']['facet_fields']['ill_flag'];
        if ($ill_facet['IllFlag.Loan'] != 0 || $ill_facet['IllFlag.Copy'] != 0 || $ill_facet['IllFlag.Ecopy'] != 0)
              return true;
       
        // Case 2
        $ill_region = $json['facet_count']['facet_field']['ill_region'];

        // In how many regions is it available ??
        $region_values = array_values($ill_region);
        $total_available = array_reduce($region_values, $this->add);

        // If there are more than 5 available items in all regions 
        // we simply assume that one of them is for loan
        if ($total_availble > 5)
            return true;

        // Case 3
        //...
 
        return false;
    } 


    public function add($carry, $item) {
         $carry += $item;
         return $carry;

    }


    public function loadAction()
    {

        $NO_ISBN = "0000000";
        $query = $this->getRequest()->getUri()->getQuery();
        $parameters = [];
        parse_str($query, $parameters);
        $isbn = !empty($parameters['isbn']) ? $parameters['isbn'] : $NO_ISBN;

        try {
            $pda_available = ($isbn != $NO_ISBN) ? (!$this->isAvailableForILL($isbn)) : false;
        } catch (Exception $e) {
          echo 'We got exception' . $e->getMessage() . "\n";
        }
        
        $pda_status = $pda_available ? "OFFER_PDA" : "NO_OFFER_PDA";
        $json = json_encode(['isbn' => $isbn,
                             'pda_status' => $pda_status]);

        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent($json);
        return $response;


//        $client = $this->getServiceLocator()->get('VuFind\Http')->createClient();
//        $client->setUri('http://gvi.bsz-bw.de/solr/GVI/select?rows=10&wt=json&facet=true&facet.field=ill_flag&facet.field=ill_region&fl=id&q=isbn:9783540658887');

//        return $client->send();
    }  
}
