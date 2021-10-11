<?php

$form = new \Nette\Forms\Form();
$form->addText('text', "Text label");
$form->addPassword('password', "Password label");
$form->addTextArea('textarea', "Textarea label");
$form->addUpload('upload', "Upload label");
$form->addCheckbox('checkbox', "Checkbox label");
$form->addRadioList('radio', "Radio label", [
	"1. radio item",
	"2. radio item",
	"3. radio item",
]);
$form->addSelect('select', "Select label", [
	"1. select item",
	"2. select item",
	"3. select item",
]);
$form->addMultiSelect('multiselect', "MultiSelect label", [
	"1. multi-select item",
	"2. multi-select item",
	"3. multi-select item",
]);
$form->addSubmit('submit', "Submit label");
$form->addButton('button', "Button label");
$form->addImage('image', '/path/to/file', "Image alt");
$form->addGroup('Group caption');

$control = $form->addText('text');
$control->setRequired("Please complete mandatory field.");
$control->setValue("Text input value");
$control->setDefaultValue("Default text input value");
$control->addError("Text input error");
$control->addRule(\Nette\Forms\Form::FILLED, "Please provide a value.");
