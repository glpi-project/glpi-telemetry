<?php

declare(strict_types=1);

namespace App\Telemetry;

enum ChartSerie
{
    case GlpiVersion;
    case InstallMode;
    case WebEngine;
    case OsFamily;
    case PhpInfos;
    case TopPlugin;
    case DefaultLanguage;
    case DbEngine;
    public function getSqlQuery(): string
    {
        switch ($this) {
            case ChartSerie::GlpiVersion:
                $sql = <<<SQL
                    SELECT SUBSTRING_INDEX(glpi_version, '.', 2) as name,
                    COUNT(DISTINCT glpi_uuid) as total
                    FROM telemetry
                    WHERE glpi_version NOT LIKE '%dev'
                    AND created_at BETWEEN :startDate AND :endDate
                    GROUP BY name
                SQL;
                return $sql;
            case ChartSerie::InstallMode:
                $sql = <<<SQL
                    SELECT install_mode as name,
                    COUNT(DISTINCT glpi_uuid) as total
                    FROM telemetry
                    WHERE created_at BETWEEN :startDate AND :endDate
                    GROUP BY name
                SQL;
                return $sql;
            case ChartSerie::WebEngine:
                $sql = <<<SQL
                    SELECT web_engine as name,
                    COUNT(DISTINCT glpi_uuid) as total
                    FROM telemetry
                    WHERE created_at BETWEEN :startDate AND :endDate
                    AND web_engine IS NOT NULL
                    GROUP BY name
                SQL;
                return $sql;
            case ChartSerie::OsFamily:
                $sql = <<<SQL
                    SELECT os_family as name,
                    COUNT(DISTINCT glpi_uuid) as total
                    FROM telemetry
                    WHERE created_at BETWEEN :startDate AND :endDate
                    GROUP BY name
                SQL;
                return $sql;
            case ChartSerie::PhpInfos:
                $sql = <<<SQL
                    SELECT SUBSTRING_INDEX(php_version, '.', 2) as name,
                    COUNT(DISTINCT glpi_uuid) as total
                    FROM telemetry
                    WHERE created_at BETWEEN :startDate AND :endDate
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
                    WHERE tgp.created_at BETWEEN :startDate AND :endDate
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
                    WHERE created_at BETWEEN :startDate AND :endDate
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
                    WHERE created_at BETWEEN :startDate AND :endDate
                    GROUP BY name
                SQL;
                return $sql;

            default:
                throw new \RuntimeException();
        }
    }
}
