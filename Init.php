<?php declare(strict_types=1);

namespace FacturaScripts\Plugins\ErrorTracking;

use FacturaScripts\Core\Base\InitClass;
use FacturaScripts\Core\Tools;
use Sentry\Event;
use Sentry\EventId;
use Sentry\Severity;

require_once __DIR__ . '/vendor/autoload.php';

class Init extends InitClass
{
    public function init(): void
    {
        $dns = Tools::settings('errortracking', 'dns');

        if (false === FS_DEBUG && false === is_null($dns)) {

            $pathMyFiles = FS_FOLDER . DIRECTORY_SEPARATOR . 'MyFiles';
            if (false === file_exists($pathMyFiles)) {
                mkdir($pathMyFiles, 0755, true);
            }

            $pathErrorLogFile = $pathMyFiles . DIRECTORY_SEPARATOR . 'ErrorTrackingPlugin.json';
            if (false === file_exists($pathErrorLogFile)) {
                // si el archivo ErrorTrackingPluginLogs.json no existe
                // añadimos todos los archivos que exista para que no se envien masivamente
                // errores antiguos y se empiece a enviar solo errores a partir de ahora.
                touch($pathErrorLogFile);
                file_put_contents($pathErrorLogFile, json_encode(glob($pathMyFiles . DIRECTORY_SEPARATOR . "crash_*.json")));
            }

            $archivosEnviados = json_decode(file_get_contents($pathErrorLogFile));
            $archivosEnDisco = glob($pathMyFiles . DIRECTORY_SEPARATOR . "crash_*.json");
            $archivosParaEnviar = array_diff($archivosEnDisco, $archivosEnviados);

            if (count($archivosParaEnviar) > 0) {
                \Sentry\init([
                    'dsn' => $dns,
                    'traces_sample_rate' => 1.0,
                    'profiles_sample_rate' => 1.0,
                ]);

                foreach ($archivosParaEnviar as $archivoParaEnviar) {
                    $datos = json_decode(file_get_contents($archivoParaEnviar), true);

                    $mensaje = '';
                    $mensaje .= trim(explode('Stack trace:', $datos['message'])[0]) . "\n\n";
                    if (isset(explode('Stack trace:', $datos['message'])[1])) {
                        $mensaje .= "Stack trace:\n" . trim(explode('Stack trace:', $datos['message'])[1]) . "\n\n";
                    }

                    unset($datos['message'], $datos['report_qr']);

                    foreach ($datos as $key => $value) {
                        $mensaje .= $key . ": " . $value . "\n";
                    }

                    $mensaje = htmlspecialchars_decode($mensaje);

                    $event = Event::createEvent();
                    $event->setLevel(Severity::fatal());
                    $event->setTransaction($datos['url']);
                    $event->setTag('file', (string)$datos['file']);
                    $event->setTag('code', (string)$datos['code']);
                    $event->setTag('core_version', (string)$datos['core_version']);
                    $event->setTag('php_version', (string)$datos['php_version']);
                    $event->setTag('os', (string)$datos['os']);
                    $event->setTag('plugin_list', (string)$datos['plugin_list']);
                    $event->setTag('url', (string)$datos['url']);
                    $event->setMessage($mensaje);
                    $event->setServerName(gethostname());

                    $respuesta = \Sentry\captureEvent($event);
                    if ($respuesta instanceof EventId) {
                        array_push($archivosEnviados, $archivoParaEnviar);
                    }
                }

                file_put_contents($pathErrorLogFile, json_encode($archivosEnviados));
            }
        }
    }

    public function update(): void
    {
        //
    }
}
