<?php

declare(strict_types=1);

namespace App\Telemetry;

use DateTimeImmutable;

enum ChartSerie: string
{
    case GlpiVersion     = 'glpi_version';
    case InstallMode     = 'install_mode';
    case WebEngine       = 'web_engine';
    case OsFamily        = 'os_family';
    case PhpInfos        = 'php_version';
    case TopPlugin       = 'top_plugin';
    case DefaultLanguage = 'glpi_default_language';
    case DbEngine        = 'db_engine';

    public function getSqlQuery(?DateTimeImmutable $startDate, ?DateTimeImmutable $endDate): string
    {
        if ($startDate !== null && $endDate !== null) {
            $baseFilter = <<<SQL
                telemetry.created_at BETWEEN '{$startDate->format('Y-m-d H:i:s')}' AND '{$endDate->format('Y-m-d H:i:s')}'
            SQL;
        } elseif ($startDate !== null) {
            $baseFilter = <<<SQL
                telemetry.created_at >= '{$startDate->format('Y-m-d H:i:s')}'
            SQL;
        } elseif ($endDate !== null) {
            $baseFilter = <<<SQL
                telemetry.created_at <= '{$endDate->format('Y-m-d H:i:s')}'
            SQL;
        } else {
            $baseFilter = <<<SQL
                true
            SQL;
        }

        $defaultGroup = <<<SQL
            GROUP BY name
        SQL;

        $defaultOrder = <<<SQL
            ORDER BY value DESC
        SQL;

        switch ($this) {
            case ChartSerie::GlpiVersion:
                $sql = <<<SQL
                    SELECT SUBSTRING_INDEX(glpi_version, '.', 2) as name,
                    COUNT(DISTINCT glpi_uuid) as value
                    FROM telemetry
                    WHERE $baseFilter
                    $defaultGroup
                    $defaultOrder
                SQL;
                return $sql;
            case ChartSerie::InstallMode:
                // `install_mode` has been introduced in GLPI 9.2.1
                // and therefore is `null` for entries related to GLPI 9.2.0
                $sql = <<<SQL
                    SELECT install_mode as name,
                    COUNT(DISTINCT glpi_uuid) as value
                    FROM telemetry
                    WHERE $baseFilter
                    AND install_mode IS NOT NULL
                    $defaultGroup
                    $defaultOrder
                SQL;
                return $sql;
            case ChartSerie::WebEngine:
                $sql = <<<SQL
                    SELECT web_engine as name,
                    COUNT(DISTINCT glpi_uuid) as value
                    FROM telemetry
                    WHERE $baseFilter
                    AND web_engine IS NOT NULL
                    AND web_engine != ''
                    $defaultGroup
                    $defaultOrder
                SQL;
                return $sql;
            case ChartSerie::OsFamily:
                $sql = <<<SQL
                    SELECT
                    CASE
                        WHEN os_family LIKE '%SunOS%' THEN 'SunOS'
                        WHEN os_family LIKE '%Linux%' THEN 'Linux'
                        ELSE os_family
                    END as name,
                    COUNT(DISTINCT glpi_uuid) as value
                    FROM telemetry
                    WHERE $baseFilter
                    $defaultGroup
                    $defaultOrder
                SQL;
                return $sql;
            case ChartSerie::PhpInfos:
                $sql = <<<SQL
                    SELECT SUBSTRING_INDEX(php_version, '.', 2) as name,
                    COUNT(DISTINCT glpi_uuid) as value
                    FROM telemetry
                    WHERE $baseFilter
                    $defaultGroup
                    $defaultOrder
                SQL;
                return $sql;
            case ChartSerie::TopPlugin:
                $sql = <<<SQL
                    SELECT pkey as name,
                    COUNT(DISTINCT telemetry.glpi_uuid) as value
                    FROM telemetry_glpi_plugin as tgp
                    INNER JOIN glpi_plugin as gp
                    ON tgp.glpi_plugin_id = gp.id
                    INNER JOIN telemetry
                    ON tgp.telemetry_entry_id = telemetry.id
                    WHERE $baseFilter
                    $defaultGroup
                    $defaultOrder
                SQL;
                return $sql;
            case ChartSerie::DefaultLanguage:
                $sql = <<<SQL
                    SELECT glpi_default_language as name,
                    COUNT(DISTINCT glpi_uuid) as value
                    FROM telemetry
                    WHERE $baseFilter
                    AND glpi_default_language REGEXP '^[a-z]{2}(_([A-Z]{2}|419))?$'
                    $defaultGroup
                    $defaultOrder
                SQL;
                return $sql;
            case ChartSerie::DbEngine:
                $sql = <<<SQL
                    SELECT
                    CASE
                        WHEN UPPER(db_engine) LIKE '%MYSQL%' THEN 'MySQL'
                        WHEN UPPER(db_engine) LIKE '%POSTGRES%' THEN 'PostgreSQL'
                        WHEN UPPER(db_engine) LIKE '%PERCONA%' THEN 'Percona'
                        WHEN UPPER(db_engine) LIKE '%MARIA%' THEN 'MariaDB'
                        WHEN UPPER(db_version) LIKE '%POSTGRES%' THEN 'PostgreSQL'
                        WHEN UPPER(db_version) LIKE '%PERCONA%' THEN 'Percona'
                        WHEN UPPER(db_version) LIKE '%MARIA%' THEN 'MariaDB'
                        ELSE 'MySQL'
                    END as name,
                    COUNT(DISTINCT glpi_uuid) as value
                    FROM telemetry
                    WHERE $baseFilter
                    $defaultGroup
                    $defaultOrder
                SQL;
                return $sql;

            default:
                throw new \RuntimeException();
        }
    }

    public function getTitle(): string
    {
        switch ($this) {
            case ChartSerie::GlpiVersion:
                return 'GLPI versions';

            case ChartSerie::InstallMode:
                return 'Installation modes';

            case ChartSerie::WebEngine:
                return 'Web engines';

            case ChartSerie::OsFamily:
                return 'Operating systems';

            case ChartSerie::PhpInfos:
                return 'PHP versions';

            case ChartSerie::TopPlugin:
                return 'Top plugins';

            case ChartSerie::DefaultLanguage:
                return 'GLPI default languages';

            case ChartSerie::DbEngine:
                return 'DB engines';

            default:
                throw new \RuntimeException();
        }
    }

    public function getChartType(): ChartType
    {
        switch ($this) {
            case ChartSerie::GlpiVersion:
            case ChartSerie::PhpInfos:
                return ChartType::MonthlyStackedBar;

            case ChartSerie::InstallMode:
            case ChartSerie::WebEngine:
            case ChartSerie::OsFamily:
            case ChartSerie::DefaultLanguage:
            case ChartSerie::DbEngine:
                return ChartType::Pie;

            case ChartSerie::TopPlugin:
                return ChartType::NightingaleRose;

            default:
                throw new \RuntimeException();
        }
    }
}
