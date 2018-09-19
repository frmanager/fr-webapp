<?php

// src/App/Utils/ValidationHelper.php

namespace App\Utils;

class ValidationHelper
{
    public static $level_pass = 'success';
    public static $level_warning = 'warning';
    public static $level_error = 'danger';

    private $errorMessage = '';
    private $errorField = '';
    private $errorFieldValue = '';

    private $isFailure = false;
    private $errorLevel = '';
    private $rowIndex = 0;
    private $entity = '';

    public function __construct(array $objects)
    {
        if (isset($objects['error_message'])) {
            $this->setErrorMessage($objects['error_message']);
        }

        if (isset($objects['entity'])) {
            $this->setEntity($objects['entity']);
        }

        if (isset($objects['error_field'])) {
            $this->setErrorField($objects['error_field']);
        }

        if (isset($objects['error_field_value'])) {
            $this->setErrorFieldValue($objects['error_field_value']);
        }

        if (isset($objects['row_index'])) {
            $this->setRowIndex($objects['row_index']);
        }

        if (isset($objects['is_Failure'])) {
            $this->setIsFailure($objects['is_Failure']);
        }

        if (isset($objects['error_level'])) {
            $this->setErrorLevel($objects['error_level']);
        }
    }

    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function setErrorField($errorField)
    {
        $this->errorField = $errorField;
    }

    public function getErrorField()
    {
        return $this->errorField;
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function setErrorFieldValue($errorFieldValue)
    {
        $this->errorFieldValue = $errorFieldValue;
    }

    public function getErrorFieldValue()
    {
        return $this->errorFieldValue;
    }

    public function setIsFailure($isFailure)
    {
        $this->isFailure = $isFailure;
    }

    public function getIsFailure()
    {
        return $this->isFailure;
    }

    public function setErrorLevel($errorLevel)
    {
        $this->errorLevel = $errorLevel;
    }

    public function getErrorLevel()
    {
        return $this->errorLevel;
    }

    public function setRowIndex($rowIndex)
    {
        $this->rowIndex = $rowIndex;
    }

    public function getRowIndex()
    {
        return $this->rowIndex;
    }

    public function getMap()
    {
        $objects = [];

        if (null !== $this->getErrorMessage()) {
            $objects['error_message'] = $this->getErrorMessage();
        }

        if (null !== $this->getEntity()) {
            $objects['entity'] = $this->getEntity();
        }

        if (null !== $this->getErrorField()) {
            $objects['error_field'] = $this->getErrorField();
        }

        if (null !== $this->getErrorFieldValue()) {
            $objects['error_field_value'] = $this->getErrorFieldValue();
        }

        if (null !== $this->getRowIndex()) {
            $objects['row_index'] = $this->getRowIndex();
        }

        if (null !== $this->getIsFailure()) {
            $objects['is_Failure'] = $this->getIsFailure();
        }

        if (null !== $this->getErrorLEvel()) {
            $objects['error_level'] = $this->getErrorLEvel();
        }

        return $objects;
    }

    public function printFlashBagMessage()
    {
        if (empty($this->getErrorFieldValue())) {
            $errorFieldValue = 'NULL';
        } else {
            $errorFieldValue = $this->getErrorFieldValue();
        }

        return '[Row #'.$this->getRowIndex().']: Field "'.$this->getErrorField().'" ['.$errorFieldValue.'] - '.$this->getErrorMessage();
    }
}
