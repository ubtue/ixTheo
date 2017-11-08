<?php

namespace ixTheo\RecordDriver;

class SolrDefault extends \TueLib\RecordDriver\SolrMarc
{
    /**
     * Get a highlighted corporation string, if available.
     *
     * @return string
     */
    public function getHighlightedCorporation(){
        // Don't check for highlighted values if highlighting is disabled:
        if (!$this->highlight) {
            return '';
        }
        return (isset($this->highlightDetails['corporation'][0]))
            ? $this->highlightDetails['corporation'][0] : '';
    }

    /**
     * Get the issue of the current record.
     *
     * @return string
     */
    public function getIssue()
    {
        return isset($this->fields['issue']) ?
            $this->fields['issue'] : '';
    }

    /**
     * Get the pages of the current record.
     *
     * @return string
     */
    public function getPages()
    {
        return isset($this->fields['pages']) ?
            $this->fields['pages'] : '';
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
        if (!empty($subtitle)) { $title .= ' : ' . $subtitle; }
        if (!empty($titleSection)) { $title .= ' / ' . $titleSection; }
        return $title;
    }

    /**
     * Get the volume of the current record.
     *
     * @return string
     */
    public function getVolume()
    {
        return isset($this->fields['volume']) ?
            $this->fields['volume'] : '';
    }

    /**
     * Get the year of the current record.
     *
     * @return string
     */
    public function getYear()
    {
        return isset($this->fields['year']) ?
            $this->fields['year'] : '';
    }

    /**
     * Get an array of publication detail lines combining information from
     * getPublicationDates(), getPublishers()
     *
     * @return array
     */
    public function getPublicationDetailsNoPlaces(){
        $names = $this->getPublishers();
        $dates = $this->getHumanReadablePublicationDates();

        $i = 0;
        $retval = [];
        while (isset($names[$i]) || isset($dates[$i])) {
            // Build objects to represent each set of data; these will
            // transform seamlessly into strings in the view layer.
            $retval[] = new \VuFind\RecordDriver\Response\PublicationDetails(
                isset($names[$i]) ? $names[$i] : '',
                isset($dates[$i]) ? $dates[$i] : '',
                null
            );
            $i++;
        }

        return $retval;
    }

    /**
     * Get secondary author and its role in a '$'-separated string
     *
     * @return array
     */
    public function getSecondaryAuthorsAndRole(){
        return isset($this->fields['author2_and_role']) ?
            $this->fields['author2_and_role'] : [];
    }

    /**
     * Get an array of all secondary authors (complementing getPrimaryAuthors()).
     *
     * @return array
     */
    public function getSecondaryAuthors()
    {
        if (!isset($this->fields['author2_and_role']))
            return [];

        $authors = array();
        foreach ($this->fields['author2_and_role'] as $author_and_roles) {
            $parts = explode('$', $author_and_roles);
            $authors[] = $parts[0];
        }

        return $authors;
    }

    /**
     * Get an array of all secondary authors roles (complementing
     * getPrimaryAuthorsRoles()).
     *
     * @return array
     */
    public function getSecondaryAuthorsRoles()
    {
        if (!isset($this->fields['author2_and_role']))
            return [];

        $roles = array();
        foreach ($this->fields['author2_and_role'] as $author_and_roles) {
            $parts = explode('$', $author_and_roles);
            $roles[] = array_slice($parts, 1);
        }

        return $roles;
    }

    /**
     * Helper function to restructure author arrays including relators
     *
     * @param array $authors Array of authors
     * @param array $roles   Array with relators of authors
     *
     * @return array
     */
    protected function getAuthorRolesArray($authors = [], $roles = [])
    {
        $authorRolesArray = [];

        if (!empty($authors)) {
            foreach ($authors as $index => $author) {
                if (!isset($authorRolesArray[$author])) {
                    $authorRolesArray[$author] = [];
                }
                if (isset($roles[$index]) && !empty($roles[$index])) {
                    if (is_array($roles[$index]))
                        $authorRolesArray[$author] = $roles[$index];
                    else
                        $authorRolesArray[$author][] = $roles[$index];
                }
            }
        }

        return $authorRolesArray;
    }

    /**
     * Get corporation.
     *
     * @return array
     */
    public function getCorporation()
    {
        return isset($this->fields['corporation']) ?
            $this->fields['corporation'] : [];
    }

    /**
     * Get the title of the item that contains this record (i.e. MARC 773s of a
     * journal).
     *
     * @return string
     */
    public function getJournalIssue()
    {
        return isset($this->fields['journal_issue'])
            ? $this->fields['journal_issue'] : '';
    }

    /**
     * Return an associative array of URL's mapped to their material types.
     *
     * @return array
     */
    public function getURLsAndMaterialTypes()
    {
        $retval = [];
        if (isset($this->fields['urls_and_material_types']) && !empty($this->fields['urls_and_material_types'])) {
            foreach ($this->fields['urls_and_material_types'] as $url_and_material_type) {
                $last_colon_pos = strrpos($url_and_material_type, ":");
                if ($last_colon_pos) {
                    $material_type = substr($url_and_material_type, $last_colon_pos + 1);
                    $retval[substr($url_and_material_type, 0, $last_colon_pos)] = $material_type;
                }
            }
        }
        return $retval;
    }

    private static function HasChapter(int $code) {
        return (intdiv($code, 1000) % 1000) != 0;
    }

    private static function HasVerse(int $code) {
        return ($code % 1000) != 0;
    }

    private static function GetBookCode(int $code) {
        return intdiv($code, 1000000);
    }

    private static function GetChapter(int $code) {
        return intdiv($code, 1000) % 1000;
    }

    private static function GetVerse(int $code) {
        return $code % 1000;
    }

    private static $codes_to_book_abbrevs = array(
        1 => "Mt",
        2 => "Mk",
        3 => "Lk",
        4 => "Jn",
        5 => "Acts",
        6 => "Rom",
        7 => "1 Cor",
        8 => "2 Cor",
        9 => "Gal",
        10 => "Eph",
        11 => "Phil",
        12 => "Col",
        13 => "1 Thess",
        14 => "2 Thess",
        15 => "1 Tim",
        16 => "2 Tim",
        17 => "Titus",
        18 => "Philemon",
        19 => "Heb",
        20 => "Jas",
        21 => "1 Pet",
        22 => "2 Pet",
        23 => "1 Jn",
        24 => "2 Jn",
        25 => "3 Jn",
        26 => "Jude",
        27 => "Rev",
        28 => "Gen",
        29 => "Ex",
        30 => "Lev",
        31 => "Num",
        32 => "Deut",
        33 => "Josh",
        34 => "Judg",
        35 => "Ruth",
        36 => "1 Sam",
        37 => "2 Sam",
        38 => "1 Kings",
        39 => "2 Kings",
        40 => "1 Chr",
        41 => "2 Chr",
        42 => "Ezra",
        43 => "Neh",
        44 => "Eth1",
        45 => "Job",
        46 => "Ps",
        47 => "Prov",
        48 => "Ecc1",
        49 => "Song",
        50 => "Isa",
        51 => "Jer",
        52 => "Lam",
        53 => "Ezek",
        54 => "Dan",
        55 => "Hos",
        56 => "Joel",
        57 => "Am",
        58 => "Obadiah",
        59 => "Jon",
        60 => "Mic",
        61 => "Nah",
        62 => "Hab",
        63 => "Zeph",
        64 => "Hag",
        65 => "Zech",
        66 => "Mal",
        67 => "3 Ezra",
        68 => "4 Ezra",
        69 => "1 Macc",
        70 => "2 Macc",
        71 => "3 Macc",
        72 => "4 Macc",
        73 => "Tob",
        74 => "Jdt",
        75 => "Bar",
        77 => "Sir",
        78 => "Wis",
        81 => "6 Macc",
        82 => "5 Ezra",
        83 => "6 Ezra",
        84 => "",
        85 => "",
    );

    private static function DecodeBookCode(int $book_code, string $separator) {
        global $codes_to_book_abbrevs;
    
        $book_code_as_string = $codes_to_book_abbrevs[GetBookCode($book_code)];
        if (!HasChapter($book_code))
            return $book_code_as_string;
        $book_code_as_string .= " " . strval(GetChapter($book_code));
        if (!HasVerse($book_code))
            return $book_code_as_string;
        return $book_code_as_string . $separator . strval(GetVerse($book_code));
    }

    private static function BibleRangeToDisplayString(string $bible_range, string $language_code) {
        global $codes_to_book_abbrevs;

        $separator = (substr($language_code, 0, 2) == "de") ? "." : ":"; 
        $code1 = (int)substr($bible_range, 0, 8);
        $code2 = (int)substr($bible_range, 9, 8);

        if ($code1 == $code2)
            return DecodeBookCode($code1, $separator);
        if (GetBookCode($code1) != GetBookCode($code2))
            return DecodeBookCode($code1, $separator) . " – " . DecodeBookCode($code2, $separator);

        $codes_as_string = $codes_to_book_abbrevs[GetBookCode($code1)] . " ";
        $chapter1 = GetChapter($code1);
        $chapter2 = GetChapter($code2);
        if ($chapter1 == $chapter2) {
            $codes_as_string .= strval($chapter1) . $separator;
            return $codes_as_string . strval(GetVerse($code1)) . "–" . strval(GetVerse($code2));
        }
        return $codes_as_string . strval($chapter1) . "–" . strval($chapter2);
    }

    public function getBibleRangesString() {
        if (!isset($this->fields['bible_ranges']))
            return "";

        $language_code = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $bible_references = "";
        foreach (explode(',', $this->fields['bible_ranges']) as $bible_range) {
            if (!empty($bible_references))
                $bible_references .= ", ";
            $bible_references .= BibleRangeToDisplayString($bible_range, $language_code);
        }
        return $bible_references;
    }
}
