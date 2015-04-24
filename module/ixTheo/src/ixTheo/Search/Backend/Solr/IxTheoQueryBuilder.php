<?php

namespace ixTheo\Search\Backend\Solr;

use VuFindSearch\Backend\Solr\QueryBuilder;
use VuFindSearch\Query\AbstractQuery;

define("_BIB_REF_MAPS_PATH_", '/usr/local/vufind2/module/ixTheo/bibRefMaps/');
define("_BIB_REF_CMD_PARAMS_", implode(' ', [_BIB_REF_MAPS_PATH_ . 'books_of_the_bible_to_code.map', _BIB_REF_MAPS_PATH_ . 'books_of_the_bible_to_canonical_form.map',  _BIB_REF_MAPS_PATH_ . 'pericopes_to_codes.map']));

class IxTheoQueryBuilder extends QueryBuilder
{
    const BIBLE_REFERENCE_COMMAND = '/bin/bib_ref_to_codes_tool';
    const BIBLE_REFERENCE_COMMAND_PARAMETERS = _BIB_REF_CMD_PARAMS_;

    public function build(AbstractQuery $query) {
        $queryString = $query->getString();
        
        $this->manipulateQuery($query);
        $result =  parent::build($query);

        $query->setString($queryString);
        return $result;
    }

    private function manipulateQuery(AbstractQuery $query) {
        $bibleReferences = $this->parseBibleReference($query);
        if ($this->isValidBibleReference($bibleReferences)) {
            $bibleQuery = $this->translateToSearchString($bibleReferences);
            $query->setString($bibleQuery);
        }
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
        return implode(' <br> ', $bibleReferences);
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