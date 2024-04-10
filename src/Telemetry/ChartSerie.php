<?php

declare(strict_types=1);

namespace App\Telemetry;

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

    public function getSqlQuery(): string
    {
        // TODO Remove invalid values from DB once migration from PgSQL will be done
        // and filter values based on official release list once a day or at submit time.
        $baseFilter = <<<SQL
            (
                telemetry.created_at BETWEEN :startDate AND :endDate
                AND telemetry.glpi_version NOT LIKE '%dev'
                AND telemetry.glpi_version REGEXP '^(9|10)\.[0-9]+'
            )
        SQL;

        switch ($this) {
            case ChartSerie::GlpiVersion:
                $sql = <<<SQL
                    SELECT SUBSTRING_INDEX(glpi_version, '.', 2) as name,
                    COUNT(DISTINCT glpi_uuid) as total
                    FROM telemetry
                    WHERE $baseFilter
                    GROUP BY name
                SQL;
                return $sql;
            case ChartSerie::InstallMode:
                // `install_mode` has been introduced in GLPI 9.2.1
                // and therefore is `null` for entries related to GLPI 9.2.0
                $sql = <<<SQL
                    SELECT install_mode as name,
                    COUNT(DISTINCT glpi_uuid) as total
                    FROM telemetry
                    WHERE $baseFilter
                    AND install_mode IS NOT NULL
                    GROUP BY name
                SQL;
                return $sql;
            case ChartSerie::WebEngine:
                $sql = <<<SQL
                    SELECT web_engine as name,
                    COUNT(DISTINCT glpi_uuid) as total
                    FROM telemetry
                    WHERE $baseFilter
                    AND web_engine IS NOT NULL
                    AND web_engine != ''
                    GROUP BY name
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
                    COUNT(DISTINCT glpi_uuid) as total
                    FROM telemetry
                    WHERE $baseFilter
                    GROUP BY name
                SQL;
                return $sql;
            case ChartSerie::PhpInfos:
                $sql = <<<SQL
                    SELECT SUBSTRING_INDEX(php_version, '.', 2) as name,
                    COUNT(DISTINCT glpi_uuid) as total
                    FROM telemetry
                    WHERE $baseFilter
                    GROUP BY name
                SQL;
                return $sql;
            case ChartSerie::TopPlugin:
                $sql = <<<SQL
                    SELECT pkey as name,
                    COUNT('glpi_plugin_id') as total
                    FROM telemetry_glpi_plugin as tgp
                    INNER JOIN glpi_plugin as gp
                    ON tgp.glpi_plugin_id = gp.id
                    INNER JOIN telemetry
                    ON tgp.telemetry_entry_id = telemetry.id
                    WHERE $baseFilter
                    GROUP BY glpi_plugin_id
                    ORDER BY total DESC
                    LIMIT 10
                SQL;
                return $sql;
            case ChartSerie::DefaultLanguage:
                $sql = <<<SQL
                    SELECT glpi_default_language as name,
                    COUNT(DISTINCT glpi_uuid) as total
                    FROM telemetry
                    WHERE $baseFilter
                    GROUP BY name
                    ORDER BY total DESC
                    LIMIT 10
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
                    COUNT(DISTINCT glpi_uuid) as total
                    FROM telemetry
                    WHERE $baseFilter
                    GROUP BY name
                SQL;
                return $sql;

            default:
                throw new \RuntimeException();
        }
    }

    public function getTitle(): string
    {
        switch($this) {
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
}
