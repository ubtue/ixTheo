<?php

namespace ixTheo\RecordDriver;
use VuFind\Exception\LoginRequired as LoginRequiredException;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class SolrMarc extends \VuFind\RecordDriver\SolrMarc implements ServiceLocatorAwareInterface
{
    protected $serviceLocator;

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator->getServiceLocator();
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function getRecordDriverByPPN($ppn) {
        $recordLoader = $this->getServiceLocator()->get('VuFind\RecordLoader');
        return $recordLoader->load($ppn, 'Solr', false);
    }

    /**
     * Get the record ID of the current record.
     *
     * @return string
     */
    public function getRecordId()
    {
        return isset($this->fields['id']) ?
            $this->fields['id'] : '';
    }

    public function getKeyWordChains()
    {
        if (isset($this->fields['key_word_chains'])) {
            $keywordchains = $this->fields['key_word_chains'];
            // Currently topic fields are also copied
            // These contain GND numbers and other stuff that should
            // not be displayed in keywordchains
            // As to topics VuFind directly evaluates the full records
            // and can thus directly filter out number subfields
            // We lose this information when evaluating the SOLR field
            // and thus have to filter manually
            $keywordchains = preg_replace('/\sgnd\s/', '', $keywordchains);
            $keywordchains = preg_replace('/\(\w{2}-\d{3}\)[\dX-]+\s*/', '', $keywordchains);
            return $keywordchains;
        }
        else {
          return '';
        }
    }

    public function getKeyWordChainBag()
    {
        return isset($this->fields['key_word_chain_bag']) ?
            $this->fields['key_word_chain_bag'] : '';
    }

    public function getPrefix4KeyWordChainBag()
    {
        return isset($this->fields['prefix4_key_word_chain_bag']) ?
            $this->fields['prefix4_key_word_chain_bag'] : '';
    }

    /**
      * @return string
      */
    public function getPageRange()
    {
        return isset($this->fields['page_range']) ? $this->fields['page_range'] : '';
    }

    /**
     * Return an associative array of all container IDs (parents) mapped to their titles containing the record.
     *
     * @return array
     */
    public function getContainerIDsAndTitles()
    {
        $retval = array();
        if (isset($this->fields['container_ids_and_titles']) && !empty($this->fields['container_ids_and_titles'])) {
            foreach ($this->fields['container_ids_and_titles'] as $id_and_title) {
                $a = explode(chr(0x1F), str_replace("#31;", chr(0x1F), $id_and_title), 3);
                if (count($a) == 3) {
                    $retval[$a[0]] = array($a[1], $a[2]);
                }
            }
        }
        return $retval;
    }

    public function getOtherTitles() {
        return isset($this->fields['other_titles']) ?
            $this->fields['other_titles'] : array();
    }

    public function getReviews()
    {
        $retval = array();
        if (isset($this->fields['reviews']) && !empty($this->fields['reviews'])) {
            foreach ($this->fields['reviews'] as $review) {
                $a = explode(chr(0x1F), str_replace("#31;", chr(0x1F), $review), 3);
                if (count($a) == 3) {
                    $retval[$a[0]] = array($a[1], $a[2]);
                }
            }
        }
        return $retval;
    }

    public function getReviewedRecords()
    {
        $retval = array();
        if (isset($this->fields['reviewed_records']) && !empty($this->fields['reviewed_records'])) {
            foreach ($this->fields['reviewed_records'] as $review) {
                $a = explode(chr(0x1F), str_replace("#31;", chr(0x1F), $review), 3);
                if (count($a) == 3) {
                    $retval[$a[0]] = array($a[1], $a[2]);
                }
            }
        }
        return $retval;
    }


    /**
     * Get all non-standardized topics
     */
    public function getAllNonStandardizedSubjectHeadings()
    {
       return (isset($this->fields['topic_non_standardized'])) ?
            $this->fields['topic_non_standardized'] : '';
    }


    /**
     * Get all standardized topics including KWCs
     */

    public function getAllStandardizedSubjectHeadings()
    {
       return (isset($this->fields['topic_standardized'])) ?
            $this->fields['topic_standardized'] : '';
    }

    public function isSuperiorWork() {
        return $this->fields['is_superior_work'];
    }

    public function isSubscribable() {
        return $this->fields['is_subscribable'];
    }

    /**
     * Get the mediatype
     */
    public function getMediaType()
    {
        return (isset($this->fields['mediatype'])) ?
             $this->fields['mediatype'] : '';
    }

    public function getAuthorsAsString() {
        $author_implode = function ($array) {
                if (is_null($array)) {
                    return null;
                }
                return implode(", ", array_filter($array, function($entry) {
                    return empty($entry) ? false : true;
                }));
            };
        return $author_implode(array_map($author_implode, array_map("array_keys", $this->getDeduplicatedAuthors())));
    }

    public function getZDBNumber()
    {
        return (isset($this->fields['zdb_number'])) ?
            $this->fields['zdb_number'] : '';

    }
     
    public function subscribe($params, $user)
    {

        if (!$user) {
            throw new LoginRequiredException('You must be logged in first');
        }

        $table = $this->getDbTable('Subscription');
        $recordId = $this->getUniqueId();
        $userId = $user->id;

        if ($table->findExisting($userId, $recordId)) {
            return "Exists";
        }
        return $table->subscribe($userId, $recordId, $this->getTitle(), $this->getAuthorsAsString(), $this->getPublicationDates()[0]);
    }

    public function unsubscribe($params, $user)
    {
        if (!$user) {
            throw new LoginRequiredException('You must be logged in first');
        }

        $table = $this->getDbTable('Subscription');
        $recordId = $this->getUniqueId();
        $userId = $user->id;

        return $table->unsubscribe($userId, $recordId);
    }

    public function pdaSubscribe($params, $user, &$data)
    {
        if (!$user) {
            throw new LoginRequiredException('You must be logged in first');
        }

        $table = $this->getDbTable('PDASubscription');
        $recordId = $this->getUniqueId();
        $userId = $user->id;

        if ($table->findExisting($userId, $recordId)) {
            return "Exists";
        }

        $data = [$userId, $recordId, $this->getTitle(), $this->getAuthorsAsString(), $this->getPublicationDates()[0], $this->getISBNs()[0]];
        return call_user_func_array([$table, "subscribe"], $data);
    }

    public function pdaUnsubscribe($params, $user)
    {
        if (!$user) {
            throw new LoginRequiredException('You must be logged in first');
        }

        $table = $this->getDbTable('PDASubscription');
        $recordId = $this->getUniqueId();
        $userId = $user->id;

        return $table->unsubscribe($userId, $recordId);
    }

    public function canUseTAD($userId)
    {
        $formats_tad_allowed = array('Article');
        $user_allowed = $this->getDbTable('IxTheoUser')->canUseTAD($userId);
        if(!$user_allowed) {
            return false;
        }
        
        $formats = $this->getFormats();
        $intersection = array_intersect($formats_tad_allowed,$this->getFormats());
        $tad_formats_allowed = !empty($intersection);

        return $tad_formats_allowed;
    }
    
    public function getAllSubjectHeadingsFlat()
    {
        $result     = array();
        $headings   = $this->getAllSubjectHeadings();
        foreach($headings as $heading_arr) {
            $result = array_merge($result, $heading_arr);
        }
        return $result;
    }

    /**
     * @param array $properties  associative array with name => value
     * @return int
     */
    public function getChildRecordCountWithProperties($properties) {
        // Shortcut: if this record is not the top record, let's not find out the
        // count. This assumes that contained records cannot contain more records.
        if (!$this->containerLinking
            || empty($this->fields['is_hierarchy_id'])
            || null === $this->searchService
        ) {
            return 0;
        }

        $safeId         = addcslashes($this->fields['is_hierarchy_id'], '"');
        
        $query_string   = 'hierarchy_parent_id:"' . $safeId . '"';
        foreach($properties as $key => $value) {
            $query_string .= ' AND ' . $key . ':"' . addcslashes($value, '"') . '"';
        }
        
        $query = new \VuFindSearch\Query\Query(
            $query_string
        );
        return $this->searchService->search('Solr', $query, 0, 0)->getTotal();
    }
    
    public function getSuperiorRecord() {
       $_773_field = $this->getMarcRecord()->getField("773");
       if (!$_773_field)
           return NULL;
       $subfields = $this->getSubfieldArray($_773_field, ['w'], /* $concat = */false);
       if (!$subfields)
           return NULL;
       $ppn = substr($subfields[0], 8);
       if (!$ppn || strlen($ppn) != 9)
           return NULL;
       return $this->getRecordDriverByPPN($ppn);
    }

    public function isAvailableInTubingenUniversityLibrary() {
       $local_fields = $this->getMarcRecord()->getFields("LOK");
       foreach ($local_fields as $local_field) {
           $subfields = $this->getSubfieldArray($local_field, ['0', 'a'], /* $concat = */false);
           if (count($subfields) == 2 && $subfields[0] == "852" && $subfields[1] == "DE-21")
               return true;
       }

       return false;
    }

    public function getSubitoURL($broker_id) {
       $base_url = "http://www.subito-doc.de/preorder/?BI=" . $broker_id;
       switch ($this->getBibliographicLevel()) {
           case 'Monograph':
               $isbn = $this->getCleanISBN();
               if (!empty($isbn))
                   return $base_url . "&SB=" . $isbn;
               return $base_url . "&CAT=SWB&ND" . $this->getRecordId(); 
           case 'Serial':
               $zdb_number = $this->getZDBNumber();
               if (!empty($zdb_number))
                   return $base_url . "&ND=" . $zdb_number;
               $issn = $this->getCleanISSN();
               if (!empty($issn))
                   return $base_url . "&SS=" . $issn;
               break;
           case 'MonographPart':
           case 'SerialPart':
               $isbn = $this->getCleanISBN();
               $issn = $this->getCleanISSN();
               $title = $this->getTitle();
               $authors = $this->getDeduplicatedAuthors();
               $page_range = $this->getPageRange();
               $volume = $this->getVolume();
               $issue = $this->getIssue();
               $year = $this->getYear();;
               if ((!empty($isbn) || !empty($issn)) && !empty($title) && !empty($authors) && !empty($page_range)
                   && (!empty($volume) || !empty($issue)) && !empty($year))
               {
                   $title = $this->escapeHtml($title);
                   $author_list = "";
                   foreach ($authors as $author) {
                       if (!empty($author_list))
                           $author_list .= "%3B";
                       $author_list .= $this->escapeHtml($author);
                   }
                   $page_range = $this->escapeHtml($page_range);

                   $volume_and_or_issue = $this->escapeHtml($volume);
                   if (!empty($volume_and_or_issue))
                       $volume_and_or_issue .= "%2F";
                   $volume_and_or_issue .= $this->escapeHtml($issue);

                   return $base_url . (!empty($isbn) ? "&SB=" . $isbn : "&SS=" . $issn) . "&ATI=" . $title . "&AAU="
                          . $author_list . "&PG=" . $page_range . "&APY=" . $year . "&VOL=" . $volume_and_or_issue;
               }
       }

       return "";
    }

    public function stripTrailingDates($text) {
        $matches = array();
        if (!preg_match("/(\\D*)(\\d{4}).*/", $text, $matches))
            return $text;
        return rtrim($matches[1]);
    }


    /**
     * Get the full title of the record.
     *
     * @return string
     */

    public function getTitle()
    {
        $title = $this->getShortTitle();
        $subtitle = $this->getSubtitle();
        $titleSection = $this->getTitleSection();
        if (!empty($subtitle)) { 
            $separator = preg_match("/^[\\s=]+/", $subtitle) ? " " : " : ";
            $title .= $separator . $subtitle; }
        if (!empty($titleSection)) { $title .= ' / ' . $titleSection; }
        return $title;
    }

    /** Check whether a record is potentially available for PDA
     *
     * @return bool
     */

    public function isPotentiallyPDA()
    {
        return $this->fields['is_potentially_pda'];
    }

}
