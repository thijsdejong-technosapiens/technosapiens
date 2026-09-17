<?php

//only allow execution of this script from shell
if (PHP_SAPI !== 'cli' || isset($_SERVER['HTTP_USER_AGENT'])) die('This script is only allowed to be executed from the terminal.');

//load WordPress core, theme and plugins
require_once(__DIR__ . '/load-wordpress.php');

/**
 * Class UpdateAcfGroups
 */
class UpdateAcfGroups
{
    /**
     * UpdateAcfGroups constructor.
     */
    public function __construct()
    {
        $this->log('Started syncing ACF JSON files..');

        if (!function_exists('acf_get_local_json_files')) {
            $this->log('ACF is not active. Aborting script..');
        } else {
            if (!is_multisite()) $this->syncAcfFields();
            else foreach (get_sites() as $site) {
                switch_to_blog($site->blog_id);
                $this->syncAcfFields();
                restore_current_blog();
            }
        }

        $this->log('Finished syncing ACF JSON files..');
    }

    /**
     * Sync ACF fields
     * @return void
     */
    public function syncAcfFields(): void
    {
        $this->log(sprintf('Started syncing ACF JSON files for website -> %1s', get_home_url()));

        $fieldGroupJsonFiles = (array)acf_get_local_json_files();
        $fieldGroupJsonFileCount = count($fieldGroupJsonFiles);
        $dbFieldGroups = acf_get_field_groups();
        $dbFieldGroupMatches = [];

        //disable JSON files writing during sync
        acf_update_setting('json', false);

        if ($fieldGroupJsonFiles && $fieldGroupJsonFileCount > 0) {
            $this->log(sprintf('We found %d ACF field group JSON files..', $fieldGroupJsonFileCount));

            $iterationIndex = 0;
            foreach ($fieldGroupJsonFiles as $key => $path) {
                $this->log(sprintf("%d / %d - Started syncing ACF field group JSON file with key \"%s\"..", ($iterationIndex + 1), $fieldGroupJsonFileCount, $key));

                try {
                    $jsonFieldGroup = json_decode(file_get_contents($path), true);

                    //check if group already exists
                    $groupMatchId = false;
                    foreach ($dbFieldGroups as $dbFieldGroup) {
                        if ($dbFieldGroup['key'] === $key) {
                            $groupMatchId = $dbFieldGroup['ID'];
                            break;
                        }
                    }

                    //add ID so group gets updated instead of inserted when it already exists
                    if ($groupMatchId !== false) {
                        $jsonFieldGroup['ID'] = $groupMatchId;
                        $dbFieldGroupMatches[] = $groupMatchId;
                        $this->log(sprintf("%d / %d - ACF field group JSON file with key \"%s\" already exists with ID \"%d\". Updating existing field group..", ($iterationIndex + 1), $fieldGroupJsonFileCount, $key, $groupMatchId));
                    } else {
                        $this->log(sprintf("%d / %d - ACF field group JSON file with key \"%s\" does not exist yet. Inserting new field group..", ($iterationIndex + 1), $fieldGroupJsonFileCount, $key));
                    }

                    //update / insert the field group
                    if ($jsonFieldGroup) {
                        $syncResult = acf_import_field_group($jsonFieldGroup);
                        if (isset($syncResult['ID'])) $this->log(sprintf("%d / %d - Successfully synced field group JSON file with key \"%s\" and ID \"%d\"..", ($iterationIndex + 1), $fieldGroupJsonFileCount, $key, $syncResult['ID']), 'success');
                        else $this->log(sprintf("%d / %d - Failed syncing field group JSON file with key \"%s\"..", ($iterationIndex + 1), $fieldGroupJsonFileCount, $key), 'error');
                    }

                } catch (Error $error) {
                    $this->log($error->getMessage(), 'error');
                }

                $this->log(sprintf("%d / %d - Finished syncing ACF field group JSON file with key \"%s\"..", ($iterationIndex + 1), $fieldGroupJsonFileCount, $key));
                $iterationIndex++;
            }
        } else {
            $this->log('No ACF field group JSON files were found. Aborting script..');
        }

        //remove field groups that do not correspond with a local JSON file
        $groupsToDelete = array_values(array_filter($dbFieldGroups, function ($dbFieldGroup) use ($dbFieldGroupMatches) {
            return !in_array($dbFieldGroup['ID'], $dbFieldGroupMatches) && $dbFieldGroup['ID'] !== 0;
        }));

        $groupsToDeleteCount = count($groupsToDelete);

        if ($groupsToDeleteCount === 0) {
            $this->log('We found no ACF field groups to delete in the database..');
        } else {
            $this->log(sprintf('We found %d ACF field groups to delete in the database..', $groupsToDeleteCount));

            foreach ($groupsToDelete as $index => $groupToDelete) {
                $this->log(sprintf("%d / %d - Started deleting ACF field group with ID \"%s\" from the database ..", ($index + 1), $groupsToDeleteCount, $groupToDelete['ID']));

                try {
                    $result = acf_delete_field_group($groupToDelete['ID']);
                    if ($result === true) {
                        $this->log(sprintf("%d / %d - Successfully deleting ACF field group with ID \"%s\" from the database ..", ($index + 1), $groupsToDeleteCount, $groupToDelete['ID']), 'success');
                    } else {
                        $this->log(sprintf("%d / %d - Failed deleting ACF field group with ID \"%s\" from the database ..", ($index + 1), $groupsToDeleteCount, $groupToDelete['ID']), 'error');
                    }
                } catch (Error $error) {
                    $this->log($error->getMessage(), 'error');
                }

                $this->log(sprintf("%d / %d - Finished deleting ACF field group with ID \"%s\" from the database..", ($index + 1), $groupsToDeleteCount, $groupToDelete['ID']));
            }
        }

        //re-enable JSON file writing
        acf_update_setting('json', true);

        $this->log(sprintf('Finished syncing ACF JSON files for website -> %1s', get_home_url()));
    }

    /**
     * Log something to the terminal
     * @param string $text
     * @param string $type \
     * @return void
     */
    public function log(string $text, string $type = 'regular'): void
    {
        $typeString = "\033[01;0m ";
        if ($type === "success") $typeString = "\033[01;32m ";
        elseif ($type === "error") $typeString = "\033[01;31m ";
        $date = $typeString . date_i18n("d-m-Y H:i:s");
        echo $date . ' - ' . $text . "\n";
    }
}

//init script
new UpdateAcfGroups();