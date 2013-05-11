<?php
$translator->gettext('A message!');
$translator->pgettext('context', 'Another message!');
$translator->ngettext('I see %d little indian!', 'I see %d little indians!', 3);
$translator->npgettext('context', 'I see %d little indian!', 'I see %d little indians!', $number);
