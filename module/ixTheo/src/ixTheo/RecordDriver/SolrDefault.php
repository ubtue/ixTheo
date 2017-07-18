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
     * Get role of secondary authors.
     *
     * @return array
     */
    public function getSecondaryAuthorsRole()
    {
        return isset($this->fields['author2-role']) ?
            $this->fields['author2-role'] : [];
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
     * Return an associative array of all container IDs (parents) mapped to their titles containing the record.
     *
     * @return array
     */
    public function getContainerIDsAndTitles()
    {
        $retval = array();
        if (isset($this->fields['container_ids_and_titles']) && !empty($this->fields['container_ids_and_titles'])) {
            foreach ($this->fields['container_ids_and_titles'] as $id_title_and_volume) {
                $a = explode("#31;", $id_title_and_volume, 3);
                if (count($a) == 3) {
                    $retval[$a[0]] = [$a[1], $a[2]];
                }
            }
        }
        return $retval;
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
}
