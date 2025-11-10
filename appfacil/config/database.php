<?php
/**
 * Configuração do banco de dados
 */

// Configurações de conexão com o banco de dados
define('DB_HOST', 'srv1782.hstgr.io');
define('DB_USER', 'u558355875_monitorobra');
define('DB_PASS', 'Zi362514*');
define('DB_NAME', 'u558355875_monitorobra');

/**
 * Classe para gerenciar a conexão com o banco de dados
 */
class Database {
    private $conn;

    /**
     * Conecta ao banco de dados
     * @return mysqli Conexão com o banco de dados
     */
    public function connect() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        // Verifica se houve erro na conexão
        if ($this->conn->connect_error) {
            die("Falha na conexão: " . $this->conn->connect_error);
        }

        // Define o charset para UTF-8
        $this->conn->set_charset("utf8");

        return $this->conn;
    }

    /**
     * Fecha a conexão com o banco de dados
     */
    public function close() {
        // Verificar se a conexão existe e está aberta antes de tentar fechá-la
        if ($this->conn instanceof mysqli && $this->conn->ping()) {
            $this->conn->close();
        }
        // Definir a conexão como null após fechá-la
        $this->conn = null;
    }
}