<?php

echo $this->translate("I see %d little indians!", 10);

echo _("Escaping some \"fancy\" text");

echo _("Some repeating text.");

echo _("Some repeating text.");

echo _p('context', 'A message with context.');
echo _n('I see %d little indian!', 'I see %d little indians!', 3);
echo _np('context', 'I see %d little indian!', 'I see %d little indians!', 3);

// PHPFilter Nette Framework integration
$form = new Form();
$form->addText('name', 'Your name:');
$form->addSubmit('ok', 'Send')
        ->onClick[] = 'OkClicked'; // nebo 'OkClickHandler'
$form->addSubmit('cancel', 'Cancel');


?>