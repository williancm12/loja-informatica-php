<?php
/******************************************************************************
Curso: Engenharia de Software
Disciplina: Linguagem e Técnicas de Programação
Professor: Flores
Turma: ESOFT2
Componentes:
            26012215-2 - Willian Cezar De Souza Moreira
            26012217-2 - Pedro Machado Gebauer
            26004820-2 - Thiago Tratch
            26011025-2 - Maria Clara Mendes Ramalho
            26014987-2 - Maria Eduarda Delbone Cardoso
Data: 21 de Outubro de 2026
Descritivo: Classe responsável pela conexão com o MySQL usando PDO. A conexão
            é criada uma única vez e reaproveitada por toda a requisição.
*******************************************************************************/

class Database
{
    // Guarda a conexão já aberta para não conectar de novo a cada consulta.
    private static $conexao = null;

    public static function getConexao(): PDO
    {
        if (self::$conexao === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NOME . ';charset=utf8mb4';

            self::$conexao = new PDO($dsn, DB_USUARIO, DB_SENHA, [
                // Erros de SQL viram exceções, em vez de falharem em silêncio.
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                // As consultas devolvem arrays associativos (coluna => valor).
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Usa prepared statements reais do MySQL (proteção contra SQL injection).
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }

        return self::$conexao;
    }
}
