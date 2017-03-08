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
use SimpleXMLElement;

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

        if (!$response->isSuccess())
            throw new Exception("HTTP ERROR"); 

        // Abort, if general JSON decoding fails
        $json = json_decode($response->getBody(), true);
        if ($json == null) 
           throw new Exception("JSON PARSE ERROR"); 

        // We use a several way scheme
        // 1.) If we have a match with ILL -> OK
        // 2.) Parse the fullrecord

        if ((!isset($json['facet_counts']['facet_fields']['ill_flag'])) || (!isset($json['facet_counts']['facet_fields']['ill_region'])))
           throw new Exception("JSON FACET FIELDs Missing"); 

        // Case 1
        $ill_facet = $json['facet_counts']['facet_fields']['ill_flag'];
        foreach ($ill_facet as $ill_flag => $count) {
              if (($ill_flag == 'IllFlag.Loan' || $ill_flag == 'IllFlag.Copy' || $ill_flag == 'IllFlag.Ecopy') && $count != 0)
                 return true;
        }
       
        // Case 2
        if (!isset($json['response']['docs']))
           throw new Exception("JSON DOCS Missing");

        $docs = $json['response']['docs'];
        foreach ($docs as $doc) {
            if (!isset($doc['fullrecord']))
               continue;
            
            // Remove escaped quotation marks
            $fullrecord = preg_replace('/[\]["]/','"', $doc['fullrecord']);
            // The "Fernleihindikator" field is 924$d. According to 
            // https://wiki.dnb.de/download/attachments/83788495/2013-10-13_MARC-Feld_924.pdf?version=1&modificationDate=1381758363000 (17/03/06)
            // we have at least 'p' (="nur Papierkopie"), 'c' (="uneingeschränkte Fernleihe), and 'd' (="keine Fernleihe")
            $xml_record = new SimpleXMLElement($fullrecord);
            foreach ($xml_record->record->children() as $datafield) {
               if ($datafield['tag'] == "924") {
                   foreach ($datafield->children() as $subfield) {
                       if ($subfield['code'] == 'd') {
                           if ($subfield == 'p' || $subfield == 'c')
                              return false;
                       }
                   }
               }
            }
        }
        return false;
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
    }  

}
