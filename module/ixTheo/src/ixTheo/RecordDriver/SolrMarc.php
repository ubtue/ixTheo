<?php

namespace ixTheo\RecordDriver;

class SolrMarc extends \ixTheo\RecordDriver\SolrDefault
{
    public function canUseTAD($userId)
    {
        $formats_tad_allowed = array('Article');
        $user_allowed = $this->getDbTable('IxTheoUser')->canUseTAD($userId);
        if(!$user_allowed) {
            return false;
        }

        $formats = $this->getFormats();
        $intersection = array_intersect($formats_tad_allowed, $this->getFormats());
        $tad_formats_allowed = !empty($intersection);

        return $tad_formats_allowed;
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

    public function getEnclosedTitles() {
        return $this->getFieldsArray([['249', ['a']], ['505', ['t']]], false);
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

    /**
      * @return string
      */
    public function getPageRange()
    {
        return isset($this->fields['page_range']) ? $this->fields['page_range'] : '';
    }

    public function getPrefix4KeyWordChainBag()
    {
        return isset($this->fields['prefix4_key_word_chain_bag']) ?
            $this->fields['prefix4_key_word_chain_bag'] : '';
    }

    /**
     * Returns persistent identifiers as array
     * e.g. array('DOI' => array(<doi1>, <doi2>),
     *            'URN' => array(<urn1>, <urn2>),);
     *
     * keys like 'DOI' will only exist if at last 1 DOI is available
     *
     * @return array
     */
    public function getTypesAndPersistentIdentifiers() {
        $result  = array();
        $rawdata = isset($this->fields['types_and_persistent_identifiers']) ? $this->fields['types_and_persistent_identifiers'] : array();

        foreach ($rawdata as $entry) {
            $entry_splitted = explode(':', $entry, 2);
            $result_type    = $entry_splitted[0];
            $result_value   = $entry_splitted[1];

            if (!isset($result[$result_type]))
                $result[$result_type] = array();
            $result[$result_type][] = $result_value;
        }

        return $result;
    }
}
