<?php

declare(strict_types=1);

namespace App\Page;

use Autodocs\Page\ReferencePage;

class ChangelogPage extends ReferencePage
{
    public array $diff = [];

    public function loadData(array $parameters = []): void
    {
        $this->diff = $parameters;
    }

    public function getName(): string
    {
        return 'changelog';
    }

    public function getSavePath(): string
    {
        return $this->autodocs->config['changelog_output'];
    }

    public function getContent(): string
    {
        $changelog = "";
        $newImages = [];
        $changed = [];

        if (is_file($this->getSavePath())) {
            $changelog = file_get_contents($this->getSavePath());
        }
        if (count($this->diff['newFiles'])) {
            foreach ($this->diff['newFiles'] as $newFile) {
                if ("yes" === $newFile['isDir']) {
                    $path = str_replace($this->autodocs->config['output'].'/', "", $newFile['path']);
                    $newImages[] = $path;
                }
            }
        }

        if (count($this->diff['changedFiles'])) {
            foreach ($this->diff['changedFiles'] as $newFile) {
                if ("no" === $newFile['isDir']) {
                    $path = str_replace($this->autodocs->config['output'].'/', "", $newFile['path']);
                    $changed[] = $path;
                }
            }
        }

        $newChangelog = "# ".date('Y-m-d')."\n";
        if (count($newImages)) {
            $newChangelog .= "New images added:\n\n- ";
            $newChangelog .= implode("\n- ", $newImages);
        }

        if (count($changed)) {
            $newChangelog .= "\n\nA total of **".count($changed)."** documents were updated.";
        }

        return $newChangelog."\n\n".$changelog;
    }
}
