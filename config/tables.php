<?php

return [
    'system' => [
        //JOINSOFTWARE ESTADOS//
        'state_types' => [
            '01' => 'Registrado',
            '03' => 'Enviado',
            '05' => 'Autorizado',
            '07' => 'Recibido',
            '09' => 'No Autorizado',
            '11' => 'Anulado',
            '13' => 'Anulando',// 'Anulación registrada',
            '15' => 'Anulando',// 'Anulación enviada',
            '30' => 'Devuelta',// 'DEVUELTA POR EL SRI',
            '31' => 'Rechazado',// 'DEVUELTA POR EL SRI',
        ],
        'soap_sends' => [
            '01' => 'Sunat',
            '02' => 'Ose',
            '03' => 'Sri',
        ],
        'soap_types' => [
            '01' => 'Demo',
            '02' => 'Producción',
        ],
        'groups' => [
            '01' => 'F',
            '02' => 'B',
        ],
        'printing_formats' => [
            'a4' => 'A4',
            'ticket' => 'Ticket'
        ]
    ],
    'tenant' => [
        'document_types' => [
            '01' => 'Factura electrónica',
            '03' => 'Boleta electrónica',
            '07' => 'Nota de crédito electrónica',
            '08' => 'Nota de débito electrónica',
        ]
    ],
];
