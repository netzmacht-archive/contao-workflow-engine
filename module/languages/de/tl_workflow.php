<?php

/**
 * legends
 */
$GLOBALS['TL_LANG']['tl_workflow']['workflow_legend']   = 'Workflow';
$GLOBALS['TL_LANG']['tl_workflow']['processes_legend']   = 'Tabellen & Prozesse';


/**
 * operations
 */
$GLOBALS['TL_LANG']['tl_workflow']['btn_services'][0] = 'Services';
$GLOBALS['TL_LANG']['tl_workflow']['btn_services'][1] = 'Services anlegen und verwalten';

$GLOBALS['TL_LANG']['tl_workflow']['new'][0] = 'Workflow anlegen';
$GLOBALS['TL_LANG']['tl_workflow']['new'][1] = 'Einen neuen Workflow anlegen';

$GLOBALS['TL_LANG']['tl_workflow']['btn_process'][0] = 'Prozesse konfigurieren';
$GLOBALS['TL_LANG']['tl_workflow']['btn_process'][1] = 'Workflow-Prozesse anlegen und konfigurieren';


/**
 * fields
 */
$GLOBALS['TL_LANG']['tl_workflow']['title'][0] = 'Titel';
$GLOBALS['TL_LANG']['tl_workflow']['title'][1] = 'Geben Sie einen Titel des Workflows an.';

$GLOBALS['TL_LANG']['tl_workflow']['processes'][0] = 'Prozesse zuweisen';
$GLOBALS['TL_LANG']['tl_workflow']['processes'][1] = 'Weisen Sie den unterstützten Tabellen einen Worfklow-Prozess zu.';

$GLOBALS['TL_LANG']['tl_workflow']['table'][0] = 'Tabelle';
$GLOBALS['TL_LANG']['tl_workflow']['table'][1] = 'Wählen Sie eine Tabelle aus.';

$GLOBALS['TL_LANG']['tl_workflow']['process'][0] = 'Prozess';
$GLOBALS['TL_LANG']['tl_workflow']['process'][1] = 'Wählen Sie einen Prozess aus, der für die Tabelle verwendet werden soll.';

$GLOBALS['TL_LANG']['tl_workflow']['workflow'][0] = 'Typ';
$GLOBALS['TL_LANG']['tl_workflow']['workflow'][1] = 'Wählen Sie einen installierten Workflow-Typ aus.';

$GLOBALS['TL_LANG']['tl_workflow']['data'][0] = 'Speicherung';
$GLOBALS['TL_LANG']['tl_workflow']['data'][1] = 'Welche Daten sollen pro Workflow-Schritt gespeichert werden.';

/**
 * Values
 */
$GLOBALS['TL_LANG']['tl_workflow']['saveData'][0][0] = 'nichts';
$GLOBALS['TL_LANG']['tl_workflow']['saveData'][0][1] = 'Es werden keine Daten gespeichert.';

$GLOBALS['TL_LANG']['tl_workflow']['saveData'][1][0] = 'Aktueller Datensatz';
$GLOBALS['TL_LANG']['tl_workflow']['saveData'][1][1] = 'Der aktuelle Datensatz der Tabelle wird gespeichert.';

$GLOBALS['TL_LANG']['tl_workflow']['saveData'][2][0] = 'Kinder-Datensätze';
$GLOBALS['TL_LANG']['tl_workflow']['saveData'][2][1] = 'Die Kinder-Datensätze des aktuellen Datensatzes speichern.';

$GLOBALS['TL_LANG']['tl_workflow']['saveData'][3][0] = 'Datensatz + Kinder';
$GLOBALS['TL_LANG']['tl_workflow']['saveData'][3][1] = 'Sowohl aktuellen Datensatz sowie Kinder-Datensätze speichern.';