<?php

namespace ixTheo\Search\Backend\Solr;

use VuFindSearch\Backend\Solr\QueryBuilder;
use VuFindSearch\Query\AbstractQuery;

define("_BIB_REF_MAPS_PATH_", '/var/lib/tuelib/');
define("_BIB_REF_CMD_PARAMS_", implode(' ', [_BIB_REF_MAPS_PATH_ . 'books_of_the_bible_to_code.map', _BIB_REF_MAPS_PATH_ . 'books_of_the_bible_to_canonical_form.map',  _BIB_REF_MAPS_PATH_ . 'pericopes_to_codes.map']));

class IxTheoQueryBuilder extends QueryBuilder
{
    const BIBLE_REFERENCE_COMMAND = '/bin/bib_ref_to_codes_tool';
    const BIBLE_REFERENCE_COMMAND_PARAMETERS = _BIB_REF_CMD_PARAMS_;

    public function build(AbstractQuery $query) {
        $queryString = $query->getString();
        if (!empty($queryString)) {
            $newQuery =  $this->getManipulatedQueryString($query);
            $result = parent::build($query);
            $result->set('q', $newQuery);
            $query->setString($queryString);
            return $result;
        }
        return parent::build($query);
    }

    private function getManipulatedQueryString(AbstractQuery $query) {
        $bibleReferences = $this->parseBibleReference($query);
        if ($this->isValidBibleReference($bibleReferences)) {
            return $this->translateToSearchString($bibleReferences);
        }
        return $query->getString();
    }

    private function parseBibleReference(AbstractQuery $query) {
        $searchQuery = $query->getString();
        if (!empty($searchQuery)) {
            $cmd = $this->getBibleReferenceCommand($searchQuery);
            exec($cmd, $output, $return_var);
            return $output;
        }
        return array();
    }

    private function translateToSearchString($bibleReferences) {
        return "{!bibleRangeParser}" . str_replace(":", "_", implode(',', $bibleReferences));
    }

    private function getBibleReferenceCommand($searchQuery) {
        return implode(' ', [
            self::BIBLE_REFERENCE_COMMAND, 
            escapeshellarg($searchQuery), 
            self::BIBLE_REFERENCE_COMMAND_PARAMETERS
        ]);
    }

    private function isValidBibleReference($bibleReferences) {
        return is_array($bibleReferences) && !empty($bibleReferences);
    }
}