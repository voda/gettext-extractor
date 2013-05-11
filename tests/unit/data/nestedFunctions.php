<?php

$translator->gettext(someFunction('Some string.'));
someFunction($translator->gettext('Nested function.'));
$translator->gettext($translator->pgettext('context', 'Nested function 2.'));
$list->add(Html::el('li')->setText(sprintf(_n("%d meeting wasn't imported.", "%d meetings weren't importeded.", $status->imported->size()), $status->imported->size())));

$form->addRule(getARule(), 'Please provide a text 2.');
$form->addRule(getARule('a parameter'), 'Please provide a text 3.');
