<?php

namespace ixTheo\RecordDriver;

class SolrMarc extends \VuFind\RecordDriver\SolrMarc
{
  /**
   * Get the record ID of the current record.
   *
   * @return string
   */
  public function getRecordID() {
      return isset($this->fields['id']) ?
         $this->fields['id'] : '';
    }

  public function getKeyWordChains() {
      return isset($this->fields['key_word_chains']) ?
         $this->fields['key_word_chains'] : '';
  }

  public function getKeyWordChainBag() {
      return isset($this->fields['key_word_chain_bag']) ?
         $this->fields['key_word_chain_bag'] : '';
  }
 
  public function getPrefix4KeyWordChainBag() {
      return isset($this->fields['prefix4_key_word_chain_bag']) ?
         $this->fields['prefix4_key_word_chain_bag'] : '';
  }
}

