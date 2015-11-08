<?php
namespace abrain\Einsatzverwaltung\Import\Sources;

use abrain\Einsatzverwaltung\Utilities;

/**
 * Abstraktion für Importquellen
 */
abstract class AbstractSource
{
    protected $actionOrder = array();
    protected $args = array();
    protected $autoMatchFields = array();
    protected $internalFields = array('post_name');
    protected $problematicFields = array();

    /**
     * @param $felder
     * @param bool|false $quiet
     */
    abstract public function checkForProblems($felder, $quiet = false);

    /**
     * @return boolean True, wenn Voraussetzungen stimmen, ansonsten false
     */
    abstract public function checkPreconditions();

    /**
     * Gibt die Beschreibung der Importquelle zurück
     *
     * @return string Beschreibung der Importquelle
     */
    abstract public function getDescription();

    /**
     * @param $action
     * @return string
     */
    public function getActionAttribute($action)
    {
        return $this->getIdentifier() . ':' . $action;
    }

    /**
     * Gibt das Action-Array für $slug zurück
     *
     * @param string $slug Slug der Action
     *
     * @return array|bool Das Array der Action oder false, wenn es keines für $slug gibt
     */
    public function getAction($slug)
    {
        if (empty($slug)) {
            return false;
        }

        foreach ($this->actionOrder as $action) {
            if ($action['slug'] == $slug) {
                return $action;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getAutoMatchFields()
    {
        return $this->autoMatchFields;
    }

    /**
     * Gibt die Einsatzberichte der Importquelle zurück
     *
     * @param array $fields Felder der Importquelle, die abgefragt werden sollen. Ist dieser Parameter null, werden alle
     * Felder abgefragt.
     *
     * @return array
     */
    abstract public function getEntries($fields);

    /**
     * @return array
     */
    abstract public function getFields();

    /**
     * Gibt die erste Action der Importquelle zurück
     *
     * @return array|bool Ein Array, das die erste Action beschreibt, oder false, wenn es keine Action gibt
     */
    public function getFirstAction()
    {
        if (empty($this->actionOrder)) {
            return false;
        }

        return $this->actionOrder[0];
    }

    /**
     * Gibt den eindeutigen Bezeichner der Importquelle zurück
     *
     * @return string Eindeutiger Bezeichner der Importquelle
     */
    abstract public function getIdentifier();

    /**
     * Gibt den Wert für das name-Attribut eines Formularelements zurück
     *
     * @param string $field Bezeichner des Felds
     * @return string Eindeutiger Name bestehend aus Bezeichnern der Importquelle und des Felds
     */
    public function getInputName($field)
    {
        return $this->getIdentifier() . '-' . $field;
    }

    /**
     * @param array $sourceFields Felder der Importquelle
     * @param array $ownFields Felder der Einsatzverwaltung
     *
     * @return array
     */
    public function getMapping($sourceFields, $ownFields)
    {
        $mapping = array();
        foreach ($sourceFields as $sourceField) {
            $index = $this->getInputName(strtolower($sourceField));
            if (array_key_exists($index, $_POST)) {
                $ownField = $_POST[$index];
                if (!empty($ownField) && is_string($ownField) && $ownField != '-') {
                    if (array_key_exists($ownField, $ownFields)) {
                        $mapping[$sourceField] = $ownField;
                    } else {
                        Utilities::printWarning("Unbekanntes Feld: $ownField");
                    }
                }
            }
        }
        foreach ($this->autoMatchFields as $sourceFieldAuto => $ownFieldAuto) {
            $mapping[$sourceFieldAuto] = $ownFieldAuto;
        }
        return $mapping;
    }

    /**
     * Gibt den Namen der Importquelle zurück
     *
     * @return string Name der Importquelle
     */
    abstract public function getName();

    /**
     * Gibt die nächste Action der Importquelle zurück
     *
     * @param array $currentAction Array, das die aktuelle Action beschreibt
     *
     * @return array|bool Ein Array, das die nächste Action beschreibt, oder false, wenn es keine weitere gibt
     */
    public function getNextAction($currentAction)
    {
        if (empty($this->actionOrder)) {
            return false;
        }

        $key = array_search($currentAction, $this->actionOrder);

        if ($key + 1 >= count($this->actionOrder)) {
            return false;
        }

        return $this->actionOrder[$key + 1];
    }

    /**
     * @return array
     */
    public function getProblematicFields()
    {
        return $this->problematicFields;
    }

    /**
     * @return array Felder, die nicht als Importziel angeboten werden sollen
     */
    public function getUnmatchableFields()
    {
        return array_merge(array_values($this->autoMatchFields), $this->internalFields);
    }

    /**
     * Setzt ein Argument in der Importquelle
     *
     * @param $key
     * @param $value
     */
    public function putArg($key, $value)
    {
        if (empty($key)) {
            return;
        }

        $this->args[$key] = $value;
    }
}
