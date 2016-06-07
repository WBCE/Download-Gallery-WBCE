<?php
/* 
 * Copyright and more information see file info.php
 */

// STEP 1:	Initialize
if (file_exists('../../config.php')) {
	require('../../config.php');   // called from within page settings
} else {
	require('../../../config.php');	// called from within module info
}

// Include WB admin wrapper script
require(WB_PATH.'/modules/admin.php');

// STEP 2:	Display the help page.
?>

<style type="text/css">
code {
	font-family:"Courier New","Courier",fixed;
	font-size:0.9em;
	font-weight:bold;
	color:#003399;
}

</style>



<div class="download_gallery_help">

	<p><strong><em><a href="#en">Jump to English version</a></em></strong></p>

	<a name="de"></a><h2>Hilfe zur Download Gallery Module 3.x (DLG3)</h2>
	
	<p>Mit der DLG3 können Sie Seiten mit Dateien zum Download erstellen. Dabei ist die Darstellung sehr flexibel anpassbar. Neben den im Backend verfügbaren Optionen kann durch Modul-Templates die Darstellung individuell angepasst werden.</p>
	
	<h3>Optionen</h3>
	<p>Im Backend können Sie verschiedene Einstellungen und Anpassungen vornehmen.</p>
	<ul>
		<li><strong>Dateien pro Seite</strong> - Hier kann festgelegt werden, dass z.B. nur 5 Dateien pro Seite angezeigt werden. Das Modul generiert dann eine Paginierung und weiter/zurück-Links zum Wechsel zwischen den Seiten.</li>
		<li><strong>Suche aktivieren</strong> - Schaltet die Filter- und Suchfunktion im Frontend ein/aus.</li>
		<li><strong>Dateigröße runden</strong> - Wenn aktiv, werden nur ganzzahlige Dateigrößen im Frontend angezeigt.</li>
		<li><strong>Dezimalstellen</strong> - Auswahl, wie viele Nachkommastellen bei der Dateigröße angezeigt werden sollen.</li>		
		<li><strong>Dateiendungen</strong> - welche Dateitypen zur Verfügung stehen und welche Icons jeweils benutzt werden sollen. </li>
		<li><strong>Verwende Templates aus Verzeichnis</strong> - Template für Frontenddarstellung auswählen (siehe nachfolgende Details).</li>
		<li><strong>Standard-CSS verwenden</strong> - Templatespezifisches Stylesheet verwenden / ignorieren.</li>
		<li><strong>Reihenfolge / Art der Sortierung</strong> - Wie die Dateien jeweils innerhalb der Gruppe sortiert werden sollen.</li>
	</ul><br />

    <p><strong>Was ist der Unterschied zwischen Filter- und Suchfunktion?</strong> <br />
    Die <strong>Filterfunktion</strong> sucht innerhalb der <strong>aktuell angezeigten</strong> Dateien nach der eingegebenen Zeichenkette und blendet interaktiv diejenigen aus, die diese nicht enthalten.<br />
    Die <strong>Suchfunktion</strong> durchsucht dagegen <strong>alle aktiven</strong> Dateien - also auch die, die auf der aktuellen Seite nicht aufgelistet sind - und zeigt das Ergebnis auf einer neuen Seite.<br />
    Bei beiden werden der Dateiname und die Beschreibung berücksichtigt.</p>
		
	<a name="layout_settings"></a><h3>Layout anpassen</h3>
	
	<p>Mit der DLG3 wurde die Handhabung der Layoutdarstellung grundlegend geändert, weshalb im Backend keine Eingabefelder mehr für die Darstellung von Seitenkopf, -fuß, Schleife usw. sind. Um die Darstellung anzupassen oder zu bearbeiten, werden statt dessen Modultemplates verwendet, die unterhalb des DLG-Modulverzeichnisses (<code>/modules/<?php echo $dlgmodname; ?></code>) im Verzeichnis <code>templates/default/frontend</code> abgelegt werden. Zum Bearbeiten/Anschauen ist somit entweder FTP-Zugang oder der <a href="http://addons.wbce.org/?do=item&item=28" target="_blank">AddonFile Editor</a> erforderlich.</p>
	
	<p>Standardmäßig mitgeliefert wird das Template <code>tableview</code> zur tabellenbasierten Darstellung der Dateien. Zur Anpassung der Darstellung müssen also die Templatedateien bearbeitet werden. <strong>Achtung:</strong> Änderungen an den Dateien unter "tableview" gehen bei einem Update des Moduls verloren, deshalb sollten Sie ein neues Verzeichnis für Ihr eigenes Template anlegen und die nachfolgend genauer beschriebenen Dateien dort hinkopieren bzw. anlegen.</p>
	
	<p>Jedes DLG3-Template besteht aus mindestens zwei Dateien: <code>files_loop.phtml</code> und <code>view.phtml</code>. Wenn das Template eigene Styles verwenden soll, so müssen diese in einer Datei namens <code>styles.css</code> im selben Verzeichnis wie die phtml-Dateien gespeichert werden.</p>
	
	<p>Die <strong>view.phtml</strong> ist im großen Ganzen eine normale HTML-Datei, enthält aber auch einige PHP-Einschübe, sodass gute HTML/CSS-Kenntnisse und auch etwas PHP-Kenntnisse erforderlich sind, um die Datei zu verstehen. Das klingt erst einmal etwas kompliziert, der meiste Code bezieht sich aber auf die Filterfunktion und Seitennavigation. </p>
	
	<p>Erklärung der PHP- und Template-Aufrufe:</p>
	<ul>
	<li><code>$DGTEXT[...]</code> Aufrufe: Sprachspezifische Ausgaben. Diese können in den Sprachdateien, also z.B. in in der /modules/<?php echo $dlgmodname; ?>/languages/de.php angepasst werden.</li>
	<li><code>if($data->settings[...]</code> Aufrufe: Prüft, welche Optionen im DLG3-Backend vorgenommen wurden. (Allgemeine Info: Bei einer if-Anweisung in dieser Templatesprache wird die Bedingung in runden Klammern angegeben. Wo in anderen Sprachen wie PHP oder JavaScript eine geschweifte öffnende Klammer gesetzt wird, wird hier ein Doppelpunkt verwendet. Jede if-Anweisung muss nach dem bei erfüllter Bedingung auszuführenden Code mit <code>&lt;?php endif; ?&gt;</code> beendet werden.)</li>
	<li><code>if($data->filecount != 0):</code> &ndash;  Den folgenden Code nur ausführen, wenn eine Paginierung erzeugt werden soll (wenn z.B. immer 5 Dateien pro Seite erscheinen sollen).</li>
	<li><code>if($data->filecount != $data->num_files):</code> &ndash;  Vergleicht die Gesamtzahl der aktiven Dateien mit der aktuell gezeigten Anzahl (um z.B. das Suchen-Feld nur anzuzeigen, wenn es mehr Dateien gibt, als aktuell angezeigt werden.)</li>
	<li><code>if($data->searchfor):</code> &ndash;  Den folgenden Code nur ausführen, wenn im Backend ausgewählt wurde, dass die Filterfunktion im Frontend angezeigt werden soll.</li>
	<li><code>&lt;?php $lastgroup = -1; ?&gt;</code> &ndash;  Erforderlich, damit die Dateigruppenheader/-footer in der files_loop.phtml an die richtige Stelle gesetzt werden.</li>
    <li><code>&lt;?php include 'files_loop.phtml' ?&gt;</code> &ndash;  Der Aufruf der Templatedatei, die für jede Gruppe/jede Datei ausgeführt werden soll. Details dazu im nächsten Absatz.</li>
	<li><code>if($data->prev): / if($data->next):</code> Den folgenden Code nur ausführen, wenn es eine vorherige / nächste Seite zum Anzeigen gibt.</li>
	<li><code>&lt;?php foreach($data->nav_pages as $number): ?&gt;</code> &ndash;  Eine Schleife, die für jede anzuzeigende Seite ausgeführt wird. (Allgemein: Dieses Konstrukt nennt sich foreach-Schleife und wird mit <code>&lt;?php endforeach; ?&gt;</code> beendet).</li>
    <li><code>&lt;?php if($number == $data->page): ?&gt; class="current"&lt?php endif; ?&gt;&gt;</code> &ndash;  Prüft, ob die jeweilige Seitennummer der gerade angezeigten Seite entspricht, und hebt diese ggf. hervor.</li>
    <li><code>&lt;a href="&lt;?php echo $data-&gt;self_link ?&gt;?page=&lt;?php echo $number?&gt;"&gt;&lt;?php echo $number?&gt;&lt;/a&gt;</code> &ndash;  Erzeugt den Link auf die einzelnen Seiten.</li>
	<li>Javascript -Block am Ende: Der Code für die Filterfunktion, d.h. bei Texteingabe werden alle Dateien, die den betr. Text nicht enthalten, ausgeblendet. Diese Funktion kann nur genutzt werden, wenn die Dateien in Tabellenform angezeigt werden.</li>
    </ul><br />        
	
	<p>Schauen wir uns nun die <strong>files_loop.phtml</strong> an.</p>
	 <ul>
	<li><code>foreach ( $data->files as $i => $file ):</code> &ndash;  Führt den folgenden Code für alle anzuzeigenden Dateien aus. Das Ende dieser foreach-Schleife steht in der letzten Zeile der files_loop.phtml.</li>
	<li><code>$image = ( isset($data->ext2img[$file['extension']]) ? $data->ext2img[$file['extension']] : 'unknown.gif' );</code> &ndash;  Prüft, ob die Dateiendung bekannt ist, und generiert den Link zum passenden Icon. Ist das nicht der Fall, wird das Standardicon für unbekannte Dateiendungen zugewiesen.</li>
	<li><code>if($lastgroup != $file['group_id']):</code> &ndash;  Prüft, ob der anzuzeigende Download zu einer anderen Gruppe als der vorherige gehört.</li>
	<li><code>if($lastgroup != -1): echo "   &lt;/tbody&gt;\n"; endif;</code> &ndash;  Schließt die zuvor angezeigte Gruppentabelle. Da auch der überhaupt allererste Download in der Liste eine andere Gruppe (nämlich überhaupt eine, im Gss zur "nicht vorhandenen" Gruppe des vorherigen "nicht vorhandenen Downloads" hat, ist diese Konstruktion mit -1 erforderlich, da sonst ein unerwünschter &gt;tbody&lt; generiert wird. </li>
	<li><code>&lt;?php echo ( isset($data-&gt;gr2name[$file['group_id']]) ? $data-&gt;gr2name[$file['group_id']] : $TEXT['NONE'] ) ?&gt;</code> &ndash;  Zeigt den Gruppentitel an, bzw. den sprachspezifischen Begriff für "keine", wenn der anzuzeigende Download keiner Gruppe zugehörig ist.</li>
	<li><code>&lt;thead id="dlg_thead_gr&lt;?php echo $file['group_id'] ?&gt;"&gt;</code> &ndash;  Jeder Gruppen-Abschnitt der Tabelle erhält seine eigene ID.</li>
	<li><code>&lt;tr&lt;?php if($i % 2): echo ' class="row_a"'; endif; ?&gt; id="td_&lt;?php echo $file['file_id'] ?&gt;"&gt;</code> &ndash;  Jede zweite Tabellenreihe erhält die KLasse  "row_a", um so eine abwechselnde Formatierung der geraden/ungeraden Zeilen zu ermöglichen. Desweiteren erhält jede Tabellenzeile ihre eigene ID, was vermutlich nützlich für die Filterung, Sortierung oder ähnliches ist.</li>
	<li><code>&lt;img src="&lt;?php echo WB_URL ?&gt;/modules/<?php echo $dlgmodname; ?>/images/&lt;?php echo $image ?&gt;" alt="" /&gt;</code> &ndash;  Zeigt das zum Dateityp passende Icon an. Diese müssen bei Übernahme dieses Codes im Verzeichnis "images" unterhalb des DLG-Modulverzeichnisses sein.</li>
	<li><code>&lt;a href="&lt;?php echo $data-&gt;self_link ?&gt;?dl=&lt;?php echo $file['file_id'] ?&gt;"&gt;&lt;?php echo $file['title'] ?&gt;&lt;/a&gt;</code> &ndash;  Gibt den Link zur Datei und den Titel des jeweiligen Downloads aus, den Sie bzw. ein_e Bearbeiter_in im Backend hinterlegt haben.</li>
	<li><code>&lt;?php echo date(DATE_FORMAT, $file['modified_when']) ?&gt;</code> &ndash;  Gibt das Änderungsdatum im Standard- bzw. benutzerspezifischen Datumsformat aus.</li>
	<li><code>&lt;?php echo ( $file['released'] != 0 ? date(DATE_FORMAT, $file['released']) : '' ) ?&gt;</code> &ndash;  Gibt das Veröffentlichungsdatum aus, sofern dies bei den Downloadeigenschaften hinterlegt wurde. </li>
	<li><code>&lt;?php echo ( ( $file['size'] &gt; 0 ) ? human_file_size($file['size']) : 0 )?&gt;</code> &ndash;  Sofern die Dateigröße nicht Null beträgt, wird diese in einem lesbaren bzw. im DLG-Backend spezifizierten Format ausgegeben (also "10 KB", "1.5  MB" usw. anstatt "21123145647612465 B").</li>
	<li><code>&lt;?php echo $file['dlcount'] ?&gt;</code> &ndash;  Gibt aus, wie oft die Datei heruntergeladen wurde (Wert kann über das Backend zurückgesetzt werden).</li>
	<li><code>&lt;?php echo $file['description'] ?&gt;</code> &ndash;  Gibt die im Backend hinterlegte Beschreibung des Downloads aus. </li>
	 </ul>
	
	<p>Auf dieselbe Weise kann übrigens auch das Backend individualisiert werden.</p>
	</div>

	<input type="button" value="<?php echo $TEXT['BACK']; ?>" onclick="javascript: window.location = '<?php echo ADMIN_URL; ?>/pages/modify.php?page_id=<?php echo $page_id; ?>';" style="width: 100px; margin-top: 5px;" />
	<hr />
	<a name="en"></a>
	<h2>Help about the Download Gallery Module 3.x (DLG3)</h2>

    <p><strong><em><a href="#de">Jump to German version / Zur deutschen Version springen</a></em></strong></p>
	
	<p>This file contains help about the different options for the DLG3. The DLG provides you the possibility to display a list of downloads within your WBCE website. As can be read down below this module is very flexible and the output can be configured in the WBCE backend on the one hand and styled and customized via template files on the other hand.</p>
	
	<a name="main_settings"></a><h3>Options</h3>
	<p>This section contains the required fields that are neccesary to get this module to work.</p>
	<ul>
		<li><strong>Files per page</strong> - When this field will be set to another value than 0 the previous/next will be shown on a page.</li>
		<li><strong>Search Filter</strong> - Whether or not the file list filter / search should be displayed.</li>
		<li><strong>Roundup file size</strong> - If checked the file size will be rounded to whole numbers.</li>
		<li><strong>Display decimals</strong> - With this option you can specify how many decimals you would like to show for the file size.</li>		
		<li><strong>File type extensions</strong> - Which file types are available and which images should be used. </li>
		<li><strong>Use templates from directory</strong> - Select template for the frontend view (see details further down)</li>
		<li><strong>Use default CSS</strong> - Activate/Deactivate template specific styles</li>
		<li><strong>Order / Order by:</strong> - How the files in the single groups should be ordered (see selectable values).</li>
	</ul><br />

    <p><strong>What's the difference between filter and search?</strong> <br />
    The <strong>filter function</strong> searches the <strong>currently shown</strong> files for the typed string and hides the files that do not match.<br />
    The <strong>search function</strong> searches the <strong>all active</strong> files for the typed string and shows the result on a new page.<br />
    Both methods take the file name and the description into account.</p>
		
	<a name="layout_settings"></a><h3>Customize layout</h3>
	<p>The way how the frontend is styled has completely changed from prior versions of the DownloadGallery module to the new DLG3. So this is the reason why you don't find dozens of textfields in the backend any more.
	To create or edit a frontend view, have a look at the module files (access via FTP or the <a href="http://addons.wbce.org/?do=item&item=28" target="_blank">AddonFile Editor</a>). As you can see, there is a new directory <code>templates/default</code> inside the <?php echo $dlgmodname; ?> folder, and this directory contains the subfolders named <code>backend</code> and <code>frontend</code>. In the frontend you find another folder, named <code>tableview</code> which is the default frontend style.</p>

	<p>Each DLG3 template consists of at least two files: <code>files_loop.phtml</code> and <code>view.phtml</code>. If the view should have its own styleseet, this has to be named <code>style.css</code> and stored in the same place like the phtml files.</p>
	
	<p>To change the tableview style, edit the named files - but <strong>caution</strong>: they will be overwritten by a module update, so you'd better create a new subfolder inside the frontend template directory and copy or create the just mentioned files there. After that, the directory is selectable in the backend dialogue of the DLG3 (option "Use templates from directory") and will be used for the frontend view when selected.</p>
	
	<p>Now let's have a look at the phtml files.</p>
	
	<p>The <strong>view.phtml</strong> is mostly a usual HTML file but containts some chunks of PHP too - so you should be familiar with HTML/CSS and need at least a bit PHP knowledge. It looks a bit complicated at the first glance, but if you have a closer look you will see that most of the code is the (complex, I confess) HTML for building the navigation and the search function. So you can completely style the output as you like.</p>

	<p>There are some placeholders which need to be explained:</p>
	<ul>
	<li><code>$DGTEXT[...]</code> stuff: The language specific outputs. You can edit them in the language files of the DLG3, e.g. /modules/<?php echo $dlgmodname; ?>/languages/de.php and so on.</li>
	<li><code>if($data->settings[...]</code> stuff: This checks which settings are made in the DLG3 backend. (General advice: The condition has to be placed in round brackets, the line ends with a double point sign (this is the place where you usually put an opening curly bracket in Javascript or PHP). Each "if" clause has to be closed with <code>&lt;?php endif; ?&gt;</code> after the code which should be executed in the case of the compliance of the the condition.)</li>
	<li><code>if($data->filecount != 0):</code> &ndash;  Execute the following code only if there should be a pagination (f.ex. show only 5 files  per page).</li>
	<li><code>if($data->filecount != $data->num_files):</code> &ndash; Compares the overall count of active files with the number of currently shown files (f.e. to hide the "search" input field if all active files are currently shown)</li>
	<li><code>if($data->searchfor):</code> &ndash;  Execute the following code only if in the backend settings the frontend search is activated.</li>
	<li><code>&lt;?php $lastgroup = -1; ?&gt;</code> &ndash;  This is needed so that the loop file knows when it has to print the table header/footer.</li>
    <li><code>&lt;?php include 'files_loop.phtml' ?&gt;</code> &ndash;  This is the template for the loop which is executed for each group/file See details in the next paragraph.</li>
	<li><code>if($data->prev): / if($data->next):</code> Execute the following code only if theres a previous/next page to display.</li>
	<li><code>&lt;?php foreach($data->nav_pages as $number): ?&gt;</code> &ndash;  A loop which is executed for each page to be generated. (Btw: A foreach loop has to be closed with <code>&lt;?php endforeach; ?&gt;</code>)</li>
    <li><code>&lt;?php if($number == $data->page): ?&gt; class="current"&lt?php endif; ?&gt;&gt;</code> &ndash;  This just checks if a number in the per-page navigation should be highlighted due to pointing to the current page.</li>
    <li><code>&lt;a href="&lt;?php echo $data-&gt;self_link ?&gt;?page=&lt;?php echo $number?&gt;"&gt;&lt;?php echo $number?&gt;&lt;/a&gt;</code> &ndash;  generates the link to the single pages.</li>
	<li>Javascript section at the end: this is the code for the search function, e.g. when typing parts of the file name in the frontend, each line which does not contain the code vanishes. This works only if the downloadable files are displayed in a HTML table.</li>
    </ul><br />        
	
	<p>Now let's switch over to the <strong>files_loop.phtml</strong>.</p>
	 <ul>
	<li><code>foreach ( $data->files as $i => $file ):</code> &ndash;  Loop to all files to display. The end of this foreach loop is in the last line of the files_loop.phtml.</li>
	<li><code>$image = ( isset($data->ext2img[$file['extension']]) ? $data->ext2img[$file['extension']] : 'unknown.gif' );</code> &ndash;  Check if the file name extension meets a known file type and use the equivalent icon. Otherwise, use the default icon for unknown file types.</li>
	<li><code>if($lastgroup != $file['group_id']):</code> &ndash;  Checks if the next file to display in the loop belongs to a different group than the one displayed before</li>
	<li><code>if($lastgroup != -1): echo "   &lt;/tbody&gt;\n"; endif;</code> Close the previously displayed group. Since the very first file in the whole list naturally has another group ID than the non-file before, we need this -1 construct to avoid generating a misplaced &gt;tbody&lt;. </li>
	<li><code>&lt;?php echo ( isset($data-&gt;gr2name[$file['group_id']]) ? $data-&gt;gr2name[$file['group_id']] : $TEXT['NONE'] ) ?&gt;</code> &ndash;  If the files belong to a group, print out the group title, otherways display the language specific term for "none".</li>
	<li><code>&lt;thead id="dlg_thead_gr&lt;?php echo $file['group_id'] ?&gt;"&gt;</code> &ndash;  Each part of the table gets its own ID.</li>
	<li><code>&lt;tr&lt;?php if($i % 2): echo ' class="row_a"'; endif; ?&gt; id="td_&lt;?php echo $file['file_id'] ?&gt;"&gt;</code> &ndash;  Every 2nd table row gets the class "row_a", so you can zebra stripe style the table. Each row for a found file gets its own ID too. Might be useful for styling, sorting, searching, whatever.</li>
	<li><code>&lt;img src="&lt;?php echo WB_URL ?&gt;/modules/<?php echo $dlgmodname; ?>/images/&lt;?php echo $image ?&gt;" alt="" /&gt;</code> &ndash;  Show the corresponding image to the file type. They are expected to be in the images directory of the DLG3. Change this according to your needs if you have nicer images stored anywhere else.</li>
	<li><code>&lt;a href="&lt;?php echo $data-&gt;self_link ?&gt;?dl=&lt;?php echo $file['file_id'] ?&gt;"&gt;&lt;?php echo $file['title'] ?&gt;&lt;/a&gt;</code> &ndash;  Displays the link to the file and the file title which you or an editor entered in the backend.</li>
	<li><code>&lt;?php echo date(DATE_FORMAT, $file['modified_when']) ?&gt;</code> &ndash;  Displays the date on which the file was modified. Use the default or user setting for the date format. </li>
	<li><code>&lt;?php echo ( $file['released'] != 0 ? date(DATE_FORMAT, $file['released']) : '' ) ?&gt;</code> &ndash;  Print out the release date if existing. (This information has to be entered by the editor when adding the file to the DLG3). Use the default or user setting for the date format.</li>
	<li><code>&lt;?php echo ( ( $file['size'] &gt; 0 ) ? human_file_size($file['size']) : 0 )?&gt;</code> &ndash;  If the file size is larger than zero print out this information in a human readable format (e.g. "10 KB", "1.5  MB" and so on instead of "21123145647612465 B").</li>
	<li><code>&lt;?php echo $file['dlcount'] ?&gt;</code> &ndash;  Displays how many times the file was downloaded. This can be resetted in the backend.</li>
	<li><code>&lt;?php echo $file['description'] ?&gt;</code> &ndash;  Displays  the description the editor has entered for this file. </li>
	 </ul>
	
	<p>BTW: you can individualize the backend in the same way.</p>
	
	
	<input type="button" value="<?php echo $TEXT['BACK']; ?>" onclick="javascript: window.location = '<?php echo ADMIN_URL; ?>/pages/modify.php?page_id=<?php echo $page_id; ?>';" style="width: 100px; margin-top: 5px;" />
    
</div>
<?php

$admin->print_footer();
?>