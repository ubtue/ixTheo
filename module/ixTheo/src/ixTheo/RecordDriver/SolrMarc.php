<?php

namespace ixTheo\RecordDriver;

class SolrMarc extends \VuFind\RecordDriver\SolrMarc
{
    /**
     * Get the record ID of the current record.
     *
     * @return string
     */
    public function getRecordID()
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
            $keywordchains = preg_replace("/\sgnd\s/", '', $keywordchains);
            $keywordchains = preg_replace("/\(\w{2}-\d{3}\)[\dX-]+\s*/", '', $keywordchains);
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
                $a = explode("#31;", $id_and_title, 3); // \u00F1 gets represented by #31;.
                if (count($a) == 3) {
                    $retval[$a[0]] = array($a[1], $a[2]);
                }
            }
        }

        return $retval;
    }

    /**
     * Return an associative array of all containee IDs (children) mapped to their titles containing the record.
     *
     * @return array
     */
    public function getContaineeIDsAndTitles()
    {
        $retval = array();
        if (isset($this->fields['containee_ids_and_titles']) && !empty($this->fields['containee_ids_and_titles'])) {
            foreach ($this->fields['containee_ids_and_titles'] as $id_and_title) {
                $a = explode(":", $id_and_title, 2);
                if (count($a) == 2) {
                    $retval[$a[0]] = $a[1];
                }
            }
        }

        return $retval;
    }

    public function isSuperiorWork() {
        return isset($this->fields['is_superior_work']);
    }
}
