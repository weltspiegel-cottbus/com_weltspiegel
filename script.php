<?php

/**
 * @package     Weltspiegel\Component\Weltspiegel
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\Filesystem\Folder;

return new class implements InstallerScriptInterface
{
    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function install(InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function update(InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function uninstall(InstallerAdapter $adapter): bool
    {
        $layoutPath = JPATH_ROOT . '/layouts/com_weltspiegel';

        if (is_dir($layoutPath)) {
            Folder::delete($layoutPath);
        }

        return true;
    }

    public function postflight(string $type, InstallerAdapter $adapter): bool
    {
        if (!\in_array($type, ['install', 'update'], true)) {
            return true;
        }

        $src  = $adapter->getParent()->getPath('source') . '/layouts/com_weltspiegel';
        $dest = JPATH_ROOT . '/layouts/com_weltspiegel';

        if (!is_dir($src)) {
            return true;
        }

        if (is_dir($dest)) {
            Folder::delete($dest);
        }

        Folder::copy($src, $dest);

        return true;
    }
};
