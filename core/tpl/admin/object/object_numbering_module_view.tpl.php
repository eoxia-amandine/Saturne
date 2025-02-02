<?php
/*
 *  Numbering module
 */
print load_fiche_titre($langs->trans('NumberingModule'), '', '');

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans('Name') . '</td>';
print '<td>' . $langs->trans('Description') . '</td>';
print '<td class="nowrap">' . $langs->trans('NextValue') . '</td>';
print '<td class="center">' . $langs->trans('Status') . '</td>';
print '</tr>';

clearstatcache();

if (empty($documentPath)) {
    $elementType = $object->element;
    $path = '/custom/' . $moduleNameLowerCase . '/core/modules/' . $moduleNameLowerCase . '/' . ($objectModSubdir ? $objectModSubdir . '/' : '') . $elementType . '/';
} else {
    $elementType = $documentParentType;
    $path = '/custom/' . $moduleNameLowerCase . '/core/modules/' . $moduleNameLowerCase . '/' . $moduleNameLowerCase . 'documents/' . $elementType . '/';
}

$dir = dol_buildpath($path);
if (is_dir($dir)) {
    $handle = opendir($dir);
    if (is_resource($handle)) {
        while (($file = readdir($handle)) !== false) {
            $filelist[] = $file;
        }
        closedir($handle);
        arsort($filelist);
        if (is_array($filelist) && !empty($filelist)) {
            foreach ($filelist as $file) {
                if (preg_match('/mod_/', $file) && preg_match('/' . $elementType . '/i', $file)) {
                    if (file_exists($dir . '/' . $file)) {
                        $classname = substr($file, 0, dol_strlen($file) - 4);

                        require_once $dir . '/' . $file;
                        $module = new $classname($db);

                        if ($module->isEnabled()) {
                            print '<tr class="oddeven"><td>';
                            print $langs->trans($module->name);
                            print '</td><td>';
                            print $module->info();
                            print '</td>';

                            // Show next value.
                            print '<td class="nowrap">';
                            $tmp = $module->getNextValue($object);
                            if (preg_match('/^Error/', $tmp)) {
                                print '<div class="error">' . $langs->trans($tmp) . '</div>';
                            } elseif ($tmp == 'NotConfigured') {
                                print $langs->trans($tmp);
                            } else {
                                print $tmp;
                            }
                            print '</td>';

                            print '<td class="center">';
                            $confType = strtoupper($moduleName) . '_' . strtoupper($elementType) . '_ADDON';
                            if ($conf->global->$confType == $file || $conf->global->$confType . '.php' == $file) {
                                print img_picto($langs->trans('Activated'), 'switch_on');
                            } else {
                                print '<a class="reposition" href="' . $_SERVER['PHP_SELF'] . '?action=setmod&value=' . preg_replace('/\.php$/', '', $file) . '&const=' . $module->scandir . '&label=' . urlencode($module->name) . '&module_name=' . $moduleName . '&token=' . newToken() . '">' . img_picto($langs->trans('Disabled'), 'switch_off') . '</a>';
                            }
                            print '</td>';

                            print '</td>';
                            print '</tr>';
                        }
                    }
                }
            }
        }
    }
}
print '</table>';
