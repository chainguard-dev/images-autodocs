<?php

declare(strict_types=1);

namespace App\Page;

use App\Image;
use Autodocs\Mark;
use Autodocs\Page\ReferencePage;
use Exception;

class VariantsPage extends ReferencePage
{
    public string $image;

    public function loadData(array $parameters = []): void
    {
        $this->image = $parameters['image'];
    }

    public function getName(): string
    {
        return 'variants';
    }

    public function getSavePath(): string
    {
        return $this->image.'/image_specs.md';
    }

    /**
     * @throws Exception
     */
    public function getContent(): string
    {
        $content = "";
        $headers = [''];
        $columns[] = [
            'Default User',
            'Entrypoint',
            'CMD',
            'Workdir',
            'Has apk?',
            'Has a shell?',
        ];

        $image = Image::loadFromDatafeed($this->autodocs->config['cache_dir'].'/datafeeds/'.$this->image.".json");
        $packages = [];

        foreach ($image->variants as $variantName => $attestation) {

            $headers[] = $variantName;
            $config = $attestation['predicate'];
            $columns[] = [
                $this->getDefaultUser($config),
                $this->getEntrypoint($config),
                $config['cmd'] ? '`'.$config['cmd'].'`' : "not specified",
                $config['work-dir'] ? '`'.$config['work-dir'].'`' : "not specified",
                $this->hasApk($config),
                $this->hasShell($config),
            ];

            //build packages array
            foreach ($config['contents']['packages'] as $dep) {
                $split = explode("=", $dep);
                $packages[$split[0]][] = $variantName;
            }
        }

        $content .= $this->getVariantsSection($this->image, $image->variants, $columns, $headers);
        $content .= "\n".$this->getDependenciesSection($packages, $headers);

        return $this->autodocs->stencil->applyTemplate('image_specs_page', [
            'title' => $this->image,
            'description' => "Detailed information about the public {$this->image} Chainguard Image.",
            'content' => $content,
        ]);
    }

    public function getEntrypoint(array $yamlConfig): string
    {
        $entrypoint = "not specified";
        if ($yamlConfig['entrypoint']['command']) {
            $entrypoint = '`'.$yamlConfig['entrypoint']['command'].'`';
        }

        if (isset($yamlConfig['entrypoint']['type']) && "service-bundle" === $yamlConfig['entrypoint']['type']) {
            $entrypoint = "Service Bundle";
        }

        return $entrypoint;
    }

    public function hasApk(array $yamlConfig): string
    {
        return $this->hasPackage(['apk-tools', 'wolfi-base'], $yamlConfig['contents']['packages']) ? "yes" : "no";
    }

    public function hasShell(array $yamlConfig): string
    {
        return $this->hasPackage(['busybox', 'bash', 'wolfi-base'], $yamlConfig['contents']['packages']) ? "yes" : "no";
    }

    public function getDefaultUser(array $yamlConfig): string
    {
        if ( ! isset($yamlConfig['accounts']['users']) ||
            ! isset($yamlConfig['accounts']['run-as']) ||
            "0" === $yamlConfig['accounts']['run-as'] ||
            "" === $yamlConfig['accounts']['run-as']
        ) {
            return '`root`';
        }

        $uid = $yamlConfig['accounts']['run-as'];
        $runAs = "";

        //locate user
        foreach ($yamlConfig['accounts']['users'] as $user) {
            if ((string)$user['uid'] === $uid) {
                $runAs = $user['username'];
                break;
            }
        }

        if ( ! $runAs) {
            $runAs = $uid;
        }

        return "`{$runAs}`";
    }

    public function hasPackage(string|array $packageName, array $packages): bool
    {
        $result = array_filter($packages, function ($value) use ($packageName) {
            $split = explode('=', $value);
            if (is_array($packageName)) {
                return in_array($split[0], $packageName);
            }

            return $split[0] === $packageName;
        });

        return (bool)count($result);
    }

    public function getVariantsSection(string $image, array $variants, array $columns, array $headers): string
    {
        $content = "";
        $tableRows = [];
        for ($i = 0; $i < sizeof($columns[0]); $i++) {
            $row = [];
            for ($j = 0; $j < sizeof($columns); $j++) {
                $row[] = $columns[$j][$i];
            }
            $tableRows[] = $row;
        }

        $content .= Mark::table($tableRows, $headers);
        $content .= "\nCheck the [tags history page](/chainguard/chainguard-images/reference/".$image."/tags_history/) for the full list of available tags.";

        return $content;
    }

    public function getDependenciesSection(array $packages, array $headers): string
    {
        $content = "\n## Packages Included\n";
        $content .= "The table shows package distribution across variants.\n\n";

        $tableRows = [];
        $row = [];
        foreach ($packages as $name => $package) {
            $row[] = '`'.$name.'`';
            for ($i = 1; $i < sizeof($headers); $i++) {
                $row[] = in_array($headers[$i], $package) ? "X" : " ";
            }
            $tableRows[] = $row;
            $row = [];
        }
        $content .= Mark::table($tableRows, $headers);

        return $content;
    }
}
