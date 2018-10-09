<?php

namespace App\Form\DataMapper;

use Symfony\Component\Form\DataMapperInterface;

class ParentToChildMapper implements DataMapperInterface
{
    private $field;

    public function __construct(string $field_name = null)
    {
        $this->field = $field_name;
    }

    public function mapDataToForms($data, $forms) : void
    {
        foreach ($forms as $field) {
            if (!$this->field || $field->getName() == $this->field) {
                $field->setData($data);
                break;
            }
        }
    }

    public function mapFormsToData($forms, &$data) : void
    {
        foreach ($forms as $field) {
            if (!$this->field || $field->getName() == $this->field) {
                $data = $field->getData();
                break;
            }
        }
    }
}
