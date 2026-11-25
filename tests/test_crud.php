<?php

/**
 * Teste de integração do DF POO usando MySQL/MariaDB real.
 * Execução, a partir da raiz do projeto: php tests/test_crud.php
 * Requer que o usuário configurado possa criar/apagar um banco de teste.
 * Nenhuma credencial é alterada; nenhuma linha do banco original é modificada.
 * Cada execução cria um banco df_poo_test_<aleatório>, reverte a transação
 * e remove somente esse banco criado pelo próprio teste.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

ini_set('display_errors', '0');
$projectRoot = dirname(__DIR__);
// Permite executar o runner preparado antes de incluí-lo no projeto.
if (!is_file($projectRoot . '/app/Config/Conexao.php')) {
    $projectRoot = $argv[1] ?? '';
}
if (!is_file($projectRoot . '/app/Config/Conexao.php')) {
    fwrite(STDERR, "FALHA: execute a partir do projeto ou informe a pasta como argumento.\n");
    exit(1);
}

require_once $projectRoot . '/app/Config/Conexao.php';
require_once $projectRoot . '/app/Controllers/UsuarioController.php';
require_once $projectRoot . '/app/Controllers/DiagnosticoController.php';

$pdo = null;
$database = null;
$sourceDatabase = null;
$created = false;
$passed = [];
$stage = 'conectar ao MySQL';
$failure = null;
$cleanupFailure = false;

function verify(bool $condition, string $label): void
{
    global $passed;
    if (!$condition) {
        throw new RuntimeException('Verificação não atendida.');
    }
    $passed[] = $label;
}

function recordById(array $records, int $id, string $getter): ?object
{
    foreach ($records as $record) {
        if ($record->$getter() === $id) {
            return $record;
        }
    }
    return null;
}

function expectInvalid(callable $action, string $label, ?string $message = null): void
{
    try {
        $action();
    } catch (InvalidArgumentException $error) {
        verify($message === null || $error->getMessage() === $message, $label);
        return;
    }
    throw new RuntimeException('Validação esperada não ocorreu.');
}

function sourceCounts(PDO $pdo): array
{
    // Confere apenas quantidades; nenhum dado pessoal é lido ou exibido.
    $result = [];
    foreach (['usuario', 'diagnostico'] as $table) {
        $result[$table] = (int) $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    }
    return $result;
}

try {
    // Suprime eventuais mensagens internas da classe de conexão.
    ob_start(static fn(string $output): string => '');
    try {
        $pdo = Conexao::conectar();
    } finally {
        ob_end_clean();
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sourceDatabase = $pdo->query('SELECT DATABASE()')->fetchColumn();
    if (!is_string($sourceDatabase) || $sourceDatabase === '') {
        throw new RuntimeException('Banco original não identificado.');
    }
    $originalCounts = sourceCounts($pdo);

    $stage = 'criar banco isolado de testes';
    $database = 'df_poo_test_' . bin2hex(random_bytes(8));
    if (!preg_match('/\Adf_poo_test_[a-f0-9]{16}\z/', $database)) {
        throw new RuntimeException('Nome de teste inválido.');
    }
    $pdo->exec("CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $created = true;
    $pdo->exec("USE `$database`");
    $sql = file_get_contents($projectRoot . '/database/diagnose_facil.sql');
    if (!is_string($sql)) {
        throw new RuntimeException('SQL de criação ausente.');
    }
    // Usa o schema entregue com o projeto sem executar CREATE DATABASE/USE originais.
    $sql = preg_replace('/CREATE\s+DATABASE\b[^;]*;/i', '', $sql);
    $sql = preg_replace('/\bUSE\s+[^;]*;/i', '', $sql);
    foreach (explode(';', $sql) as $statement) {
        if (trim($statement) !== '') {
            $pdo->exec($statement);
        }
    }
    verify($pdo->query('SELECT DATABASE()')->fetchColumn() === $database, 'Conexão direcionada apenas ao banco isolado');
    $stage = 'executar CRUD e regras de relacionamento';
    $pdo->beginTransaction();
    $users = new UsuarioController($pdo);
    $diagnostics = new DiagnosticoController($pdo);

    verify($users->listar() === [] && $diagnostics->listar() === [], 'Banco de teste começa sem registros');
    $userId = $users->cadastrar('Pessoa de Teste', 'teste1@example.invalid', null);
    $secondId = $users->cadastrar('Outra Pessoa', 'teste2@example.invalid', '11999990000');
    verify($userId > 0 && $secondId > 0 && $secondId !== $userId, 'Criar usuários com IDs distintos');
    $user = recordById($users->listar(), $userId, 'getIdUsuario');
    verify($user !== null && $user->getNome() === 'Pessoa de Teste' && $user->getEmail() === 'teste1@example.invalid' && $user->getTelefone() === null, 'Ler usuário e telefone opcional');
    verify($users->atualizar($userId, 'Pessoa Atualizada', 'atualizado@example.invalid', '11888880000'), 'Atualizar usuário');
    $user = recordById($users->listar(), $userId, 'getIdUsuario');
    verify($user !== null && $user->getNome() === 'Pessoa Atualizada' && $user->getEmail() === 'atualizado@example.invalid' && $user->getTelefone() === '11888880000', 'Ler os dados atualizados do usuário');

    $diagnosticId = $diagnostics->cadastrar($userId, 'A torneira da cozinha está pingando.');
    verify($diagnosticId > 0, 'Criar diagnóstico vinculado ao usuário');
    $diagnostic = recordById($diagnostics->listar(), $diagnosticId, 'getIdDiagnostico');
    verify($diagnostic !== null && $diagnostic->getIdUsuario() === $userId && $diagnostic->getDescricao() === 'A torneira da cozinha está pingando.' && $diagnostic->getDataDiagnostico() !== null, 'Ler diagnóstico com vínculo e data');

    expectInvalid(fn() => $users->excluir($userId), 'Controller bloqueia exclusão de usuário com diagnóstico', 'Este usuário possui diagnósticos e não pode ser excluído.');
    verify(recordById($users->listar(), $userId, 'getIdUsuario') !== null && recordById($diagnostics->listar(), $diagnosticId, 'getIdDiagnostico') !== null, 'Bloqueio preserva usuário e diagnóstico');

    verify($diagnostics->atualizar($diagnosticId, $secondId, "Relato atualizado com acento, & e <texto>.\nSegunda linha."), 'Atualizar descrição e usuário do diagnóstico');
    $diagnostic = recordById($diagnostics->listar(), $diagnosticId, 'getIdDiagnostico');
    verify($diagnostic !== null && $diagnostic->getIdUsuario() === $secondId && $diagnostic->getDescricao() === "Relato atualizado com acento, & e <texto>.\nSegunda linha.", 'Ler descrição integral e novo relacionamento');
    verify($users->excluir($userId), 'Excluir usuário que já não possui diagnósticos');
    verify(recordById($users->listar(), $userId, 'getIdUsuario') === null, 'Usuário excluído deixa a listagem');
    expectInvalid(fn() => $users->excluir($secondId), 'Bloqueio acompanha o novo usuário vinculado');

    $foreignKeyBlocked = false;
    try {
        $diagnostics->cadastrar($userId, 'Usuário já excluído, relacionamento deve ser rejeitado.');
    } catch (PDOException $error) {
        $foreignKeyBlocked = $error->getCode() === '23000';
    }
    verify($foreignKeyBlocked, 'MySQL rejeita diagnóstico com usuário inexistente por chave estrangeira');
    expectInvalid(fn() => $users->cadastrar('', 'invalido'), 'Campos obrigatórios do usuário validados');
    expectInvalid(fn() => $users->cadastrar('Teste', 'email-invalido'), 'E-mail inválido rejeitado');
    expectInvalid(fn() => $diagnostics->cadastrar($secondId, '  '), 'Descrição obrigatória validada');
    expectInvalid(fn() => $diagnostics->atualizar(0, $secondId, 'Relato'), 'Identificador inválido rejeitado');

    verify($diagnostics->excluir($diagnosticId), 'Excluir diagnóstico');
    verify(recordById($diagnostics->listar(), $diagnosticId, 'getIdDiagnostico') === null, 'Diagnóstico excluído deixa a listagem');
    verify($users->excluir($secondId), 'Excluir usuário após remover seu diagnóstico');
    verify($users->listar() === [] && $diagnostics->listar() === [], 'CRUD completo termina sem registros de teste');

    // Deixa linhas novas dentro da transação para comprovar o rollback também.
    $rollbackUserId = $users->cadastrar('Rollback', 'rollback@example.invalid');
    $diagnostics->cadastrar($rollbackUserId, 'Registro temporário para verificar a reversão.');
    $pdo->rollBack();
    verify($users->listar() === [] && $diagnostics->listar() === [], 'Rollback remove todos os registros temporários');

    $stage = 'conferir preservação do banco original';
    $pdo->exec('USE `' . str_replace('`', '``', $sourceDatabase) . '`');
    verify(sourceCounts($pdo) === $originalCounts, 'Quantidades do banco original preservadas');
} catch (Throwable $error) {
    // Nunca expõe texto da exceção ou informações da conexão.
    $failure = 'FALHA na etapa: ' . $stage . ' (' . get_class($error) . ').';
} finally {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        try {
            $pdo->rollBack();
        } catch (Throwable $error) {
            $cleanupFailure = true;
        }
    }
    if ($created && $pdo instanceof PDO && is_string($database) && preg_match('/\Adf_poo_test_[a-f0-9]{16}\z/', $database)) {
        try {
            $pdo->exec('USE `' . str_replace('`', '``', $sourceDatabase) . '`');
            $pdo->exec("DROP DATABASE `$database`");
        } catch (Throwable $error) {
            $cleanupFailure = true;
        }
    }
}

foreach ($passed as $label) {
    fwrite(STDOUT, 'OK: ' . $label . "\n");
}
if ($failure !== null) {
    fwrite(STDERR, $failure . "\n");
}
if ($cleanupFailure) {
    fwrite(STDERR, 'FALHA: não foi possível remover o banco de teste ' . $database . ".\n");
}
if ($failure !== null || $cleanupFailure) {
    exit(1);
}
fwrite(STDOUT, 'PASSOU: ' . count($passed) . " verificações; banco original preservado e banco isolado removido.\n");
