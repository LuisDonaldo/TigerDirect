<?php

class WP_Postalcodeoptionalfix_Model_Customer_Form extends Mage_Customer_Model_Form
{
    /**
     * Validate data array and return true or array of errors
     *
     * @param array $data
     * @return boolean|array
     */
    public function validateData(array $data)
    {
        $errors = array();
        foreach ($this->getAttributes() as $attribute) {
            if ($this->_isAttributeOmitted($attribute)) {
                continue;
            }
            $dataModel = $this->_getAttributeDataModel($attribute);
            $dataModel->setExtractedData($data);
            if (!isset($data[$attribute->getAttributeCode()])) {
                $data[$attribute->getAttributeCode()] = null;
            }
            $aCode = $attribute->getAttributeCode();
            switch ($aCode) {
                case 'postcode':
                    if (isset($data['country_id']) && Mage::helper('directory')->isZipCodeOptional($data['country_id'])) {
                        $result = true;
                    } else {
                        $result = $dataModel->validateValue($data[$aCode]);
                    }
                    break;

                default:
                    $result = $dataModel->validateValue($data[$aCode]);
            }
            if ($result !== true) {
                $errors = array_merge($errors, $result);
            }
        }
        if (count($errors) == 0) {
            return true;
        }
        return $errors;
    }
}
