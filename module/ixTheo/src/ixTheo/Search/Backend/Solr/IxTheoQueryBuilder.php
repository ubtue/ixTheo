<?php

namespace ixTheo\Search\Backend\Solr;

use VuFindSearch\Backend\Solr\QueryBuilder;
use VuFindSearch\Query\AbstractQuery;

class IxTheoQueryBuilder extends QueryBuilder
{
    const BIBLE_REFERENCE_COMMAND = '/bin/bib_ref_parser_test';

    public function build(AbstractQuery $query) {
        $queryString = $query->getString();
        
        $this->manipulateQuery($query);
        $result =  parent::build($query);

        $query->setString($queryString);
        return $result;
    }

    private function manipulateQuery(AbstractQuery $query) {
        $bibleReferences = $this->parseBibleReference($query);
        if (is_array($bibleReferences) && !empty($bibleReferences)) {
            $bibleQuery = $this->translateToSearchString($bibleReferences);
            $query->setString($bibleQuery);
            print_r($query->getString());
        }
    }

    private function parseBibleReference(AbstractQuery $query) {
         $args = escapeshellarg($query->getString());
         exec(self::BIBLE_REFERENCE_COMMAND . ' ' . $args, $output, $return_var);
         return $output;         
    }

    private function translateToSearchString($bibleReferences) {
        return implode(' <br> ', $bibleReferences);
    }
}