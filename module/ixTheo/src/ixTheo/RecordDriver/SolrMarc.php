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

    private function getAuthorsAsString() {
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

    public function canUseTAD($userId)
    {
        return $this->getDbTable('IxTheoUser')->canUseTAD($userId);
    }

    public function getEmailAddress($userId)
    {
        $user = $this->getDbTable('User')->getById($userId);
	return $user ? $user->email : "";
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

    private function canBeOrderedViaTADHelper($record) {
        if (!$record->isAvailableInTubingenUniversityLibrary())
            return false;

        // Exclude electronic resources:
        $_007_field = $record->getMarcRecord()->getField("007");
        if (!$_007_field || $_007_field->getData()[0] == 'c')
            return false;

        // Publication type "continuing resource" and type "newspaper" or "periodical":
        $_008_field = $record->getMarcRecord()->getField("008");
        return $_008_field && preg_match("/^.{6}(c|d).{14}(n|p)/", $_008_field->getData());
    }

    public function canBeOrderedViaTAD() {
       if ($this->canBeOrderedViaTADHelper($this))
           return true;
       $parent = $this->getSuperiorRecord();
       return $parent ? $this->canBeOrderedViaTADHelper($parent) : false;
    }
}
