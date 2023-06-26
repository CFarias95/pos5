<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantSPMayorcontable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql_create = "
           CREATE PROCEDURE SP_Mayorcontable(IN date_start DATE, IN date_end DATE, IN cuenta INT)
            BEGIN

            sET @d = date_start ;
            sET @h = date_end ;
            sET @c = cuenta ;

            SELECT   a.accounting_entrie_id AS Asiento, a.seat_line as Linea,b.code as Cuenta ,
            b.description AS  Descripcion_cuenta ,c.`comment` Comentario, c.seat_date as  fecha, c.serie AS Serie, 
            c.number as Numero,  a.debe AS Debe, a.haber AS Haber ,
            a.seat_cost AS 'C_C' , p.number AS  Id_persona, 
            p.name as Nombre_persona
            FROM  accounting_entry_items AS a LEFT JOIN account_movements AS b
            ON a.account_movement_id = b.id
            left join accounting_entries AS c ON a.accounting_entrie_id = c.seat
            left JOIN persons AS p ON c.person_id = p.id
            WHERE c.seat_date >= @d
            AND c.seat_date <= @h
            AND  (  0 =  @c  OR  b.code =  @c );

            END 
        ";
        DB::connection('tenant')->statement($sql_create);
        /**/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sql_delete = "
            DROP PROCEDURE SP_Mayorcontable;
        ";
        DB::connection('tenant')->statement($sql_delete);
    }
}
