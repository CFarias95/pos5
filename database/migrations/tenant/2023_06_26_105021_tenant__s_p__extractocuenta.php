<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantSPExtractocuenta extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql_create = "
            CREATE PROCEDURE SP_Extractocuenta(IN date_start DATE, IN date_end DATE, IN cuenta INT)
            BEGIN

            set @s  = 0.0 ;
            sET @d = date_start ;
            sET @h = date_end ;
            sET @cuenta = cuenta ;

            SELECT  AA.Asiento, AA.Linea, AA.Cuenta, AA.Descripcion_cuenta, AA.Comentario, AA.Fecha, AA.Serie,
            AA.Numero, AA.Debe, AA.Haber,
            (SELECT @s:=@s+(debe-haber)) as Saldo,
            AA.C_C, AA.Id_persona, AA.Nombre_persona
            FROM (
            SELECT   '' Asiento, 0 as Linea,'' as Cuenta ,
            '' AS  Descripcion_cuenta ,'SALDO ANTERIOR' Comentario, '' as  fecha, '' AS Serie, '' as Numero,
            SUM(a.debe) AS Debe, SUM(a.haber) AS Haber ,'' AS 'C_C' ,'' AS  Id_persona, '' as Nombre_persona
            FROM  accounting_entry_items AS a
            left join accounting_entries AS c ON a.accounting_entrie_id = c.seat
            LEFT JOIN account_movements AS b
            ON a.account_movement_id = b.id
            WHERE c.seat_date < @d AND b.code = 401010101
            UNION ALL
            SELECT   a.accounting_entrie_id AS Asiento, a.seat_line as Linea,b.code as Cuenta ,
            b.description AS  Descripcion_cuenta ,c.comment Comentario, c.seat_date as  fecha, c.serie AS Serie,
            c.number as Numero,  a.debe AS Debe, a.haber AS Haber ,
            a.seat_cost AS 'C.C' , p.number AS  Id_persona,
            p.name as Nombre_persona
            FROM  accounting_entry_items AS a LEFT JOIN account_movements AS b
            ON a.account_movement_id = b.id
            left join accounting_entries AS c ON a.accounting_entrie_id = c.seat
            left JOIN persons AS p ON c.person_id = p.id
            WHERE c.seat_date >= @d
            AND c.seat_date <= @h
            AND b.code = @cuenta
            ) AS AA
            ORDER BY AA.Cuenta ASC;


            END
        ";
        DB::connection('tenant')->statement($sql_create);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sql_delete = "
            DROP PROCEDURE IF EXISTS SP_Extractocuenta;
        ";
        DB::connection('tenant')->statement($sql_delete);
    }
}
