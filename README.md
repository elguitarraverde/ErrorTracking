# ErrorTracking

Plugin para FacturaScripts

Monitoriza los errores en https://sentry.io

## Instalación
Es necesario configurar el valor dns en el Panel de control > errortraking.

## Descripción
Sentry(https://sentry.io) es una herramienta de monitorización de errores de software en entorno de producción. Permite detectar, rastrear y solucionar errores de manera eficiente, lo que ayuda a mejorar la calidad del software y la experiencia del usuario. 

Cuando se envía un error a Sentry, este sistema registra el error con datos como (url, ruta archivo, Stack Trace, versión de php, nombre del servidor, etc.) y se envía automáticamente un correo electrónico a la persona registrada en Sentry.

Este plugin obtiene rápidamente los errores críticos de FacturaScripts y los envía a Sentry lo que permite minimizar el tiempo de detección de errores de una instalación y minimizar la mala experiencia de los usuarios el evitar que estos tengan que contactarnos para comunicarnos los errores detectados.

Es muy útil cuando tienes muchas instalaciones en producción y tienes que tener el control total de los errores críticos casi en tiempo real.

Solo funciona cuando el modo DEBUG no se encuentra activo.