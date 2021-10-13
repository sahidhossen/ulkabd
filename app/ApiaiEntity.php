<?php

namespace App;

class ApiaiEntity
{
    private $data;

    public function __construct($category, $data = null) {
        if ($data) {
            $this->data = $data;
        }
        else if ($category) {
            $this->data = [
                'name'      => $this->entityNameWithCategoryName($category->name),
                "entries"   => [],
            ];
        }
    }

    public function entityNameWithCategoryName($name) {
        if ($name && strlen($name) > 0) {
            $eName = mb_strtolower($name);
            $eName = preg_replace('/\s+/', '-', $eName);
//            $eName = preg_replace('/[^A-Za-z0-9\-]/', '', $eName);
            $eName = preg_replace('/-+/', '-', $eName);
            $eName = 'e' . substr(uniqid(sha1($eName)),0,20);

            return $eName;
        }
        else
            return null;
    }

    public function setName($name) {
        $this->data['name'] = $this->entityNameWithCategoryName($name);
    }

    public function getName() {
        return $this->data['name'];
    }

    /**
     * @return array
     */
    public function getData() {
        return $this->data;
    }

    public static function entryWith($value, $synonyms) {
        return [
            "value"     => $value,
            "synonyms"  => $synonyms
        ];
    }
}
