<?php

namespace idoit\Component\FeatureManager;

use idoit\Component\Helper\ArrayHelper;

class FeatureManager
{
    /**
     * @var iterable|Feature[] $placeholders
     */
    private iterable $features = [];

    /**
     * CollectionCategoryConfigurationSource constructor.
     *
     * @param iterable|Feature[] $features
     */
    public function __construct(iterable $features = [])
    {
        $this->features = $features;
    }

    /**
     * @return iterable
     */
    public function getFeatures(): iterable
    {
        if (!\isys_application::instance()->container->has('idoit.component.feature-manager')) {
            // prevent usage if service does not exist
            return [];
        }

        return $this->features;
    }

    /**
     * @param bool|null $cloudable
     *
     * @return array
     */
    public function getFeatureNameList(?bool $cloudable = null): array
    {
        if (!\isys_application::instance()->container->has('idoit.component.feature-manager')) {
            // prevent usage if service does not exist
            return [];
        }

        $featureList = [];
        foreach ($this->features as $feature) {
            if (($cloudable !== null && $feature->isCloudAble() !== $cloudable) ||
                trim($feature->getName()) === ''
            ) {
                continue;
            }

            $featureList[] = $feature->getName();
        }
        return $featureList;
    }

    /**
     * If service does not exist than we are not in the cloud and every feature is active
     *
     * @param string $feature
     *
     * @return bool
     */
    public static function isFeatureActive(string $feature): bool
    {
        if (!self::isCloud() || !\isys_application::instance()->container->has('idoit.component.feature-manager')) {
            return true;
        }

        global $g_active_features;

        $activeFeatures = $g_active_features ?? [];

        return is_array($activeFeatures) && in_array($feature, $activeFeatures);
    }

    /**
     * @return bool
     */
    public static function isCloud(): bool
    {
        global $g_is_cloud;

        if (isset($g_is_cloud)) {
            return (bool)$g_is_cloud;
        }

        return false;
    }

    /**
     * @return array|Feature[]
     */
    public function getActiveFeatures(): array
    {
        if (!\isys_application::instance()->container->has('idoit.component.feature-manager')) {
            // prevent usage if service does not exist
            return [];
        }

        global $g_active_features;

        $activeFeatures = $g_active_features ?? [];

        return array_filter($this->getFeatureNameList(), fn ($item) => in_array($item, $activeFeatures, true));
    }

    /**
     * @return array|Feature[]
     */
    public function getInactiveFeatures(): array
    {
        if (!\isys_application::instance()->container->has('idoit.component.feature-manager')) {
            // prevent usage if service does not exist
            return [];
        }

        global $g_active_features;

        $activeFeatures = $g_active_features ?? [];

        return array_filter($this->getFeatureNameList(), fn ($item) => !in_array($item, $activeFeatures, true));
    }

    /**
     * @return array
     */
    private function getConfigParameters(): array
    {
        global $g_comp_database_system, $g_db_system, $g_admin_auth, $g_crypto_hash, $g_disable_addon_upload, $g_enable_gui_update, $g_license_token,
        $g_security, $g_license_token, $g_is_cloud;

        // @see ID-11757 if it has not been replaced than use 0 as default
        if (empty($g_is_cloud) || '%config.cloud.active%' === $g_is_cloud) {
            $g_is_cloud = 0;
        }

        return [
            '%config.adminauth.username%'                   => array_keys($g_admin_auth)[0],
            '%config.adminauth.password%'                   => $g_admin_auth[array_keys($g_admin_auth)[0]],
            '%config.db.type%'                              => $g_db_system['type'],
            '%config.db.host%'                              => $g_db_system['host'],
            '%config.db.port%'                              => $g_db_system['port'],
            '%config.db.username%'                          => $g_db_system['user'],
            '%config.db.password%'                          => $g_db_system['pass'],
            '%config.db.name%'                              => $g_db_system['name'],
            '%config.crypt.hash%'                           => $g_crypto_hash,
            '%config.admin.disable_addon_upload%'           => $g_disable_addon_upload,
            '%config.admin.enable_gui_update%'              => $g_enable_gui_update ?? 1,
            '%config.license.token%'                        => $g_license_token,
            '%config.security.passwords_encryption_method%' => $g_security['passwords_encryption_method'],
            '%config.cloud.active%'                         => $g_is_cloud ? (int)$g_is_cloud : 0,
        ];
    }

    /**
     * @param string $featureName
     *
     * @return Feature|null
     */
    private function getFeature(string $featureName): ?Feature
    {
        foreach ($this->features as $feature) {
            if ($feature->getName() === $featureName) {
                return $feature;
            }
        }

        return null;
    }

    /**
     * @param string $feature
     *
     * @return bool
     */
    private function hasWildcard(string $feature): bool
    {
        return strpos($feature, '*') !== false;
    }

    /**
     * @param string $featureName
     *
     * @return array
     */
    private function getFeaturesByWildcard(string $featureName): array
    {
        $pattern = '/' . str_replace('*', '.*', $featureName) . '/';
        $foundFeatures = [];
        foreach ($this->features as $feature) {
            if (preg_match($pattern, $feature->getName())) {
                $foundFeatures[] = $feature->getName();
            }
        }
        return $foundFeatures;
    }

    /**
     * @param array     $features
     * @param bool|null $cloudIndicator
     *
     * @return bool
     */
    public function enableFeatures(array $features, ?bool $cloudIndicator = null): bool
    {
        if (!self::isCloud() || !\isys_application::instance()->container->has('idoit.component.feature-manager')) {
            // prevent usage if service does not exist
            return false;
        }

        global $g_absdir, $g_active_features;

        try {
            $newFeatures = $g_active_features ?? [];

            foreach ($features as $feature) {
                if ($this->hasWildcard($feature)) {
                    $newFeatures = ArrayHelper::merge($newFeatures, $this->getFeaturesByWildcard($feature));
                    continue;
                }

                $fetureObject = $this->getFeature($feature);
                if (!$fetureObject) {
                    continue;
                }

                if ($cloudIndicator !== null && $fetureObject->isCloudable() !== $cloudIndicator) {
                    continue;
                }

                $newFeatures = ArrayHelper::with($newFeatures, $feature);
            }

            $configParameters = $this->getConfigParameters();
            $configParameters['%config.active_features.list%'] = !empty($newFeatures) ? implode("','", array_filter($newFeatures)) : null;
            $g_active_features = $newFeatures;

            // Update config
            write_config($g_absdir . '/setup/config_template.inc.php', $g_absdir . '/src/config.inc.php', $configParameters);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @param array|string[] $features
     *
     * @return bool
     */
    public function disableFeatures(array $features, ?bool $cloudIndicator = null): bool
    {
        if (!self::isCloud() || !\isys_application::instance()->container->has('idoit.component.feature-manager')) {
            // prevent usage if service does not exist
            return false;
        }

        global $g_absdir, $g_active_features;

        try {
            $newFeatures = $g_active_features ?? [];
            foreach ($features as $feature) {
                if ($this->hasWildcard($feature)) {
                    $removedFeatures = $this->getFeaturesByWildcard($feature);

                    if (!empty($removedFeatures)) {
                        $newFeatures = array_filter($newFeatures, fn ($item) => !in_array($item, $removedFeatures));
                    }
                    continue;
                }

                $featureObject = $this->getFeature($feature);
                if (!$featureObject) {
                    continue;
                }

                if ($cloudIndicator !== null && $featureObject->isCloudable() !== $cloudIndicator) {
                    continue;
                }

                $newFeatures = ArrayHelper::without($newFeatures, $feature);
            }

            $configParameters = $this->getConfigParameters();
            $configParameters['%config.active_features.list%'] = !empty($newFeatures) ? implode("','", array_filter($newFeatures)) : null;
            $g_active_features = $newFeatures;

            // Update config
            write_config($g_absdir . '/setup/config_template.inc.php', $g_absdir . '/src/config.inc.php', $configParameters);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function replaceFeatures(array $features, ?bool $cloudIndicator = null): bool
    {
        if (!self::isCloud() || !\isys_application::instance()->container->has('idoit.component.feature-manager')) {
            // prevent usage if service does not exist
            return false;
        }

        global $g_absdir, $g_active_features;

        try {
            $newFeatures = [];

            foreach ($features as $feature) {
                if ($this->hasWildcard($feature)) {
                    $newFeatures = ArrayHelper::concat($newFeatures, $this->getFeaturesByWildcard($feature));
                    continue;
                }

                $featureObject = $this->getFeature($feature);
                if (!$featureObject) {
                    continue;
                }

                if ($cloudIndicator !== null && $feature->isCloudable() !== $cloudIndicator) {
                    continue;
                }

                $newFeatures[] = $feature;
            }

            $configParameters = $this->getConfigParameters();
            $configParameters['%config.active_features.list%'] = !empty($newFeatures) ? implode("','", $newFeatures) : null;
            $g_active_features = $newFeatures;

            // Update config
            write_config($g_absdir . '/setup/config_template.inc.php', $g_absdir . '/src/config.inc.php', $configParameters);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @param bool $isCloud
     *
     * @return void
     * @throws \Exception
     */
    public function setCloud(bool $isCloud): void
    {
        global $g_active_features, $g_absdir, $g_is_cloud;

        $configParameters = $this->getConfigParameters();
        $configParameters['%config.active_features.list%'] = is_array($g_active_features) ? implode("','", $g_active_features) : '';
        $configParameters['%config.cloud.active%'] = (int)$isCloud;
        $g_is_cloud = !!$isCloud;

        // Update config
        write_config($g_absdir . '/setup/config_template.inc.php', $g_absdir . '/src/config.inc.php', $configParameters);
    }
}
