<?php

/**
 *
 */
$GLOBALS['TL_LANG']['workflow']['services']['notify'][0] = 'Benachrichtigungen';
$GLOBALS['TL_LANG']['workflow']['services']['notify'][1] = 'Benachrichtigungen über Änderungen per E-Mail versenden.';

$GLOBALS['TL_LANG']['workflow']['services']['storage'][0] = 'Datensätze speichern';
$GLOBALS['TL_LANG']['workflow']['services']['storage'][1] = 'Speichert den jeweiligen Datensatz des Workflow-Models.';

$GLOBALS['TL_LANG']['workflow']['services']['parent'][0] = 'Workflow des Eltern-Datensatzes';
$GLOBALS['TL_LANG']['workflow']['services']['parent'][1] = 'Der Workflow des Elterndatensatzes wird verwendet. Dies ist beispielsweise für Inhaltselemente sinnvoll.';



/**
 * steps
 */
$GLOBALS['TL_LANG']['workflow']['steps']['created'][0] = 'Erstellt';
$GLOBALS['TL_LANG']['workflow']['steps']['created'][1] = 'Inhalt wurde erstellt.';

$GLOBALS['TL_LANG']['workflow']['steps']['changed'][0] = 'Bearbeitet';
$GLOBALS['TL_LANG']['workflow']['steps']['changed'][1] = 'Inhalt wurde bearbeitet.';

$GLOBALS['TL_LANG']['workflow']['steps']['proposed'][0] = 'Veröffentlichung angefragt';
$GLOBALS['TL_LANG']['workflow']['steps']['proposed'][1] = 'Veröffentlichung des Inhalts wurde angefragt.';

$GLOBALS['TL_LANG']['workflow']['steps']['validated'][0] = 'Für Veröffentlichung freigegeben';
$GLOBALS['TL_LANG']['workflow']['steps']['validated'][1] = 'Inhalt wurde für Veröffentlichung freigegeben.';

$GLOBALS['TL_LANG']['workflow']['steps']['published'][0] = 'Veröffentlicht';
$GLOBALS['TL_LANG']['workflow']['steps']['published'][1] = 'Inhalt wurde für veröffentlicht.';

$GLOBALS['TL_LANG']['workflow']['steps']['unpublished'][0] = 'Depubliziert';
$GLOBALS['TL_LANG']['workflow']['steps']['unpublished'][1] = 'Inhalt wurde für depubliziert.';

$GLOBALS['TL_LANG']['workflow']['steps']['deleted'][0] = 'Gelöscht';
$GLOBALS['TL_LANG']['workflow']['steps']['deleted'][1] = 'Inhalt wurde für gelöscht.';

$GLOBALS['TL_LANG']['workflow']['steps']['archived'][0] = 'Archiviert';
$GLOBALS['TL_LANG']['workflow']['steps']['archived'][1] = 'Inhalt wurde archiviert';

$GLOBALS['TL_LANG']['workflow']['steps']['aborted'][0] = 'Verworfen';
$GLOBALS['TL_LANG']['workflow']['steps']['aborted'][1] = 'Inhalt wurde gelöscht bevor er veröffentlicht wurde.';


/**
 * states
 */
$GLOBALS['TL_LANG']['workflow']['states']['create'][0] = 'erstellen';
$GLOBALS['TL_LANG']['workflow']['states']['create'][1] = 'Neen Inhalt erstellen.';

$GLOBALS['TL_LANG']['workflow']['states']['change'][0] = 'bearbeiten';
$GLOBALS['TL_LANG']['workflow']['states']['change'][1] = 'Inhalt bearbeiten.';

$GLOBALS['TL_LANG']['workflow']['states']['propose'][0] = 'einreichen';
$GLOBALS['TL_LANG']['workflow']['states']['propose'][1] = 'Veröffentlichung für Inhalt anfragen.';

$GLOBALS['TL_LANG']['workflow']['states']['reject'][0] = 'ablehnen';
$GLOBALS['TL_LANG']['workflow']['states']['reject'][1] = 'Angefragte Veröffentlichung ablehnen.';

$GLOBALS['TL_LANG']['workflow']['states']['validate'][0] = 'freigeben';
$GLOBALS['TL_LANG']['workflow']['states']['validate'][1] = 'Inhalt kann veröffentlicht werden.';

$GLOBALS['TL_LANG']['workflow']['states']['publish'][0] = 'veröffentlichen';
$GLOBALS['TL_LANG']['workflow']['states']['publish'][1] = 'Inahlt veröffentlichen.';

$GLOBALS['TL_LANG']['workflow']['states']['unpublish'][0] = 'depublizieren';
$GLOBALS['TL_LANG']['workflow']['states']['unpublish'][1] = 'Inhalt depublizieren.';

$GLOBALS['TL_LANG']['workflow']['states']['delete'][0] = 'löschen';
$GLOBALS['TL_LANG']['workflow']['states']['delete'][1] = 'Inhalt löschen.';

$GLOBALS['TL_LANG']['workflow']['states']['archive'][0] = 'archivieren';
$GLOBALS['TL_LANG']['workflow']['states']['archive'][1] = 'Inhalt archivieren';

$GLOBALS['TL_LANG']['workflow']['states']['restore'][0] = 'wiederherstellen';
$GLOBALS['TL_LANG']['workflow']['states']['restore'][1] = 'Angefragte Veröffentlichung ablehnen.';

/**
 *
 */
$GLOBALS['TL_LANG']['workflow']['roles']['owner'][0] = 'Eigentümer';
$GLOBALS['TL_LANG']['workflow']['roles']['owner'][1] = 'Autor des Inhalts';

$GLOBALS['TL_LANG']['workflow']['roles']['editor'][0] = 'Bearbeiter';
$GLOBALS['TL_LANG']['workflow']['roles']['editor'][1] = 'Nutzer mit Bearbeitungsrechten';

$GLOBALS['TL_LANG']['workflow']['roles']['reviewer'][0] = 'Prüfer';
$GLOBALS['TL_LANG']['workflow']['roles']['reviewer'][1] = '';

$GLOBALS['TL_LANG']['workflow']['roles']['publisher'][0] = 'Veröffentlicher';
$GLOBALS['TL_LANG']['workflow']['roles']['publisher'][1] = 'Benutzer darf Entwürfe veröffentlichen.';

$GLOBALS['TL_LANG']['workflow']['roles']['super'][0] = 'Super-User';
$GLOBALS['TL_LANG']['workflow']['roles']['super'][1] = 'Benutzer darf veröffentlichte Inhalte depublizieren.';