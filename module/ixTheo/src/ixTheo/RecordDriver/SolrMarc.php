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
        return isset($this->fields['key_word_chains']) ?
            $this->fields['key_word_chains'] : '';
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
}

