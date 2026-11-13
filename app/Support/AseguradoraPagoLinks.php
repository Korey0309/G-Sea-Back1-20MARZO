<?php

namespace App\Support;

/**
 * Sufijo del botón "Pagar mi póliza" (plantilla g_pruebas) → portal de la aseguradora.
 * WhatsApp abre https://gsea.company/pagar/{slug}.
 */
class AseguradoraPagoLinks
{
    /** @var array<string, string> nombre normalizado => slug */
    private const SLUGS = [
        'QUALITAS' => 'qualitas',
        'AXA' => 'axa',
        'HDI' => 'hdi',
        'CHUBB' => 'chubb',
        'GNP' => 'gnp',
        'MAPFRE' => 'mapfre',
        'ALLIANZ' => 'allianz',
        'ALIANZ' => 'allianz',
        'INBURSA' => 'inbursa',
        'SEGUROS MONTERREY' => 'monterrey',
        'MONTERREY' => 'monterrey',
    ];

    /** @var array<string, string> */
    private const URLS = [
        'qualitas' => 'https://www.qualitas.com.mx/web/qmx/pago-de-poliza',
        'axa' => 'https://cajaaxa.mitec.com.mx/cua/inicio.do?method=loginAgente&perfil=cliente',
        'hdi' => 'https://portalpagos.hdi.com.mx/identificaPoliza',
        'chubb' => 'https://clientes-seguros.mx.chubb.com/pago-poliza',
        'gnp' => 'https://www.gnp.com.mx/pagar-en-linea-mi-seguro-gnp',
        'mapfre' => 'https://mapfrepayonline.mapfre.com.mx/landing',
        'allianz' => 'https://www.allianz.com.mx/servicios/tramites/pago-en-linea.html',
        'inbursa' => 'https://asesores.inbursa.com/PortalAsesores/toEnvia.do?Acceso=1',
        'monterrey' => 'https://inv.mx/pagar',
        'general' => 'https://inv.mx/pagar',
    ];

    public static function slugFor(?string $nombreAseguradora): string
    {
        $key = self::normalize($nombreAseguradora);

        return self::SLUGS[$key] ?? 'general';
    }

    public static function urlForSlug(?string $slug): string
    {
        $slug = strtolower(trim((string) $slug));

        return self::URLS[$slug] ?? self::URLS['general'];
    }

    private static function normalize(?string $nombre): string
    {
        $nombre = trim((string) $nombre);
        $nombre = mb_strtoupper($nombre, 'UTF-8');
        $nombre = strtr($nombre, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
        ]);
        $nombre = preg_replace('/\s+/', ' ', $nombre) ?? $nombre;

        return $nombre;
    }
}
