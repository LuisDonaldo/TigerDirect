<?php

class WP_OnePageCheckout_Block_Html_Select
    extends Mage_Core_Block_Html_Select
{
    private $_columnName = '';

    public function setInputName($inputName)
    {
        $this->setName($inputName);
        return $this;
    }

    public function setColumn($column)
    {
        $this->setClass($column['class']);
        $this->setExtraParams(' style="' . $column['style'] . '" rel="#{' . $this->_columnName . '}" onchange="this.title = this.options[this.selectedIndex].text"');
        return $this;
    }

    public function setColumnName($columnName)
    {
        $this->setOptions($this->getOptionArray());
        $this->_columnName = $columnName;
        return $this;
    }
}
