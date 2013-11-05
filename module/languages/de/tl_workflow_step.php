<?php

/**
 * legends
 */
$GLOBALS['TL_LANG']['tl_workflow_step']['name_legend'] = 'Prozess-Schritt';
$GLOBALS['TL_LANG']['tl_workflow_step']['states_legend'] = 'Aktionen';
$GLOBALS['TL_LANG']['tl_workflow_step']['protect_legend'] = 'Rechtevergabe';

/**
 * fields
 */
$GLOBALS['TL_LANG']['tl_workflow_step']['name'][0] = 'Name';
$GLOBALS['TL_LANG']['tl_workflow_step']['name'][1] = 'Der Name wird intern zur Idendifikation verwendet.';

$GLOBALS['TL_LANG']['tl_workflow_step']['description'][0] = 'Beschreibung';
$GLOBALS['TL_LANG']['tl_workflow_step']['description'][1] = 'Kurzbeschreibung des Prozess-Schrittes';

$GLOBALS['TL_LANG']['tl_workflow_step']['roles'][0] = 'Rollen';
$GLOBALS['TL_LANG']['tl_workflow_step']['roles'][1] = 'Nur Benutzer mit definierten Rollen dürfen den Schritt erreichen.';

$GLOBALS['TL_LANG']['tl_workflow_step']['start'][0] = 'Prozessbeginn';
$GLOBALS['TL_LANG']['tl_workflow_step']['start'][1] = 'Schritt wird als erster Schritt des Prozesses definiert. Es kann nur ein Schritt als Prozessbeginn definiert werden.';

$GLOBALS['TL_LANG']['tl_workflow_step']['end'][0] = 'Prozessende';
$GLOBALS['TL_LANG']['tl_workflow_step']['end'][1] = 'Wird der Schritt erreicht, ist der Prozess beendet. Es sind keine weiteren Aktionen mehr möglich.';

$GLOBALS['TL_LANG']['tl_workflow_step']['next_states'][0] = 'Nächste Aktionen';
$GLOBALS['TL_LANG']['tl_workflow_step']['next_states'][1] = 'Definieren Sie Aktionen, die nach Erreichen des Prozess-Schrittes möglich sein sollen.';

$GLOBALS['TL_LANG']['tl_workflow_step']['targetType'][0] = 'Typ';
$GLOBALS['TL_LANG']['tl_workflow_step']['targetType'][1] = 'Eine Aktion kann auf einen Schritt oder einen anderen Process verlinken.';

$GLOBALS['TL_LANG']['tl_workflow_step']['target'][0] = 'Ziel';
$GLOBALS['TL_LANG']['tl_workflow_step']['target'][1] = 'Wählen Sie das Ziel aus, auf das die Aktion verlinkt.';

$GLOBALS['TL_LANG']['tl_workflow_step']['state'][0] = 'Aktion';
$GLOBALS['TL_LANG']['tl_workflow_step']['state'][1] = 'Aktion auswählen.';