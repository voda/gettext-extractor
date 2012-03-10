<?php
$translator->gettext('A message!');
$translator->pgettext('context', 'Another message!');
$translator->ngettext('I see %d little indian!', 'I see %d little indians!', 3);
$translator->npgettext('context', 'I see %d little indian!', 'I see %d little indians!', $number);

$translator->gettext($foo);
$translator->pgettext('context', $bar);
$translator->npgettext($baz, 'I see %d little indian!', 'I see %d little indians!', 3);

$translator->gettext(someFunction('Some string.'));
someFunction($translator->gettext('Nested function.'));
$translator->gettext($translator->pgettext('context', 'Nested function 2.'));
$list->add(Html::el('li')->setText(sprintf(_n("%d meeting wasn't imported.", "%d meetings weren't importeded.", $status->imported->size()), $status->imported->size())));

$form->addRule(AppForm::FILLED, 'Please provide a text.');
$form->addRule(getARule(), 'Please provide a text 2.');
$form->addRule(getARule('a parameter'), 'Please provide a text 3.');

$translator->gettext('A'.' message!');

$translator->gettext('A
message!');

$dialog->addConfirmer('delete', array($this, 'delete'), "Really delete?");
$form->addSelect('name', 'label', array(
	'item 1',
	'item 2'
));
