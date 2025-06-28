<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Mensaje requerido']);
    exit;
}

$userMessage = strtolower(trim($input['message']));

function getBotResponse($message) {
    // Respuestas predefinidas
    $responses = [
        'hola' => "¡Hola! ¿Cómo estás? ¿En qué puedo ayudarte?",
        'hi' => "¡Hola! ¿Cómo estás? ¿En qué puedo ayudarte?",
        'nombre' => "Soy ChatBot PHP, tu asistente virtual del servidor. Puedo procesar tus mensajes y responder desde el backend.",
        'tiempo' => "No tengo acceso a APIs meteorológicas, pero puedo ayudarte con otras consultas.",
        'clima' => "No tengo acceso a APIs meteorológicas, pero puedo ayudarte con otras consultas.",
        'edad' => "Soy un script PHP ejecutándose en el servidor, así que no tengo edad física.",
        'ayuda' => "Puedo ayudarte con:\n• Procesamiento de datos en el servidor\n• Respuestas desde PHP\n• Consultas generales\n• ¡Y más! Solo pregúntame.",
        'help' => "Puedo ayudarte con:\n• Procesamiento de datos en el servidor\n• Respuestas desde PHP\n• Consultas generales\n• ¡Y más! Solo pregúntame.",
        'gracias' => "¡De nada! Es un placer ayudarte desde el servidor PHP.",
        'thanks' => "¡De nada! Es un placer ayudarte desde el servidor PHP.",
        'adiós' => "¡Hasta luego! Gracias por usar el chatbot PHP. ¡Que tengas un excelente día!",
        'bye' => "¡Hasta luego! Gracias por usar el chatbot PHP. ¡Que tengas un excelente día!",
        'real madrid' => "¡El Real Madrid es increíble! Como chatbot PHP, puedo procesar información sobre el club desde el servidor.",
        'fútbol' => "¡El fútbol es genial! ¿Te gustaría que procese alguna información específica sobre equipos?",
        'programación' => "¡PHP es genial para programación web! Es mi lenguaje nativo. ¿Quieres saber algo específico sobre desarrollo?",
        'php' => "¡PHP es mi lenguaje! Es perfecto para desarrollo web del lado del servidor. ¿Tienes alguna pregunta específica?",
        'servidor' => "Estoy ejecutándome en el servidor usando PHP. Puedo procesar datos, conectar con bases de datos y más.",
        'base de datos' => "Con PHP puedo conectarme a MySQL, PostgreSQL y otras bases de datos. ¿Necesitas ayuda con consultas?",
        'mysql' => "MySQL es una excelente base de datos que funciona perfectamente con PHP. ¿Tienes alguna consulta específica?"
    ];
    
    // Buscar coincidencias exactas primero
    foreach ($responses as $key => $response) {
        if (strpos($message, $key) !== false) {
            return $response;
        }
    }
    
    // Respuestas generales si no hay coincidencias
    $generalResponses = [
        "Interesante pregunta. Como chatbot PHP, estoy procesando tu mensaje en el servidor.",
        "Desde el servidor PHP, puedo decirte que es un tema fascinante. ¿Podrías ser más específico?",
        "Tu mensaje ha sido procesado exitosamente en PHP. ¿En qué más puedo ayudarte?",
        "Como asistente PHP del servidor, encuentro tu consulta muy interesante. Cuéntame más.",
        "Procesando tu mensaje desde el backend... ¡Listo! ¿Hay algo específico que te gustaría saber?",
        "Tu consulta ha llegado al servidor PHP. ¿Te gustaría explorar este tema más a fondo?"
    ];
    
    return $generalResponses[array_rand($generalResponses)];
}

// Simular un pequeño delay para hacer más realista la respuesta
usleep(500000); // 0.5 segundos

$response = [
    'message' => getBotResponse($userMessage),
    'timestamp' => date('Y-m-d H:i:s'),
    'processed_by' => 'PHP Backend'
];

echo json_encode($response);
?>