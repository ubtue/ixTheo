<?php

namespace ixTheo\Search\Backend\Solr;

use VuFindSearch\Backend\Solr\QueryBuilder;
use VuFindSearch\Query\AbstractQuery;

define("_BIB_REF_MAPS_PATH_", '/var/lib/tuelib/bibleRef/'); // Must end with backslash!
define("_BIB_REF_CMD_PARAMS_", implode(' ', [_BIB_REF_MAPS_PATH_ . 'books_of_the_bible_to_code.map', _BIB_REF_MAPS_PATH_ . 'books_of_the_bible_to_canonical_form.map',  _BIB_REF_MAPS_PATH_ . 'pericopes_to_codes.map']));

class IxTheoQueryBuilder extends QueryBuilder
{
    const BIBLE_REFERENCE_COMMAND = '/usr/local/bin/bib_ref_to_codes_tool';
    const BIBLE_REFERENCE_COMMAND_PARAMETERS = _BIB_REF_CMD_PARAMS_;

    public function build(AbstractQuery $query) {
        // TODO: Bei Erweiterter Suche wird eine andere Query-Klasse genutzt.
        // Diese muss anders behandelt werden, da sie aus vielen Sub-Queries
        // besteht.
        // Vorerst wird die Bibelstellensuche nur bei der Standartsuche
        // angewendet.
        if (is_a($query, 'VuFindSearch\Query\QueryGroup')) {
            return parent::build($query);
        }
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
        $searchString = urlencode("{!bibleRangeParser}" . str_replace(":", "_", implode(',', $bibleReferences)));
        echo "<a href='http://ptah.ub.uni-tuebingen.de:8080/solr/#/biblio/query?q=" . $searchString . "&fl=bible_ranges,title'>In Solr-Admin-Panel suchen</a>";
        return "{!bibleRangeParser}" . str_replace(":", "_", implode(',', $bibleReferences));
    }

    private function getBibleReferenceCommand($searchQuery) {
        setlocale(LC_CTYPE, "de_DE.UTF-8");
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