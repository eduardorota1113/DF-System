
<?php

class Diagnostico
{
    private ?int $idDiagnostico;
    private int $idUsuario;
    private string $descricao;
    private ?string $dataDiagnostico;
    private ?string $categoria;
    private ?string $perigo;

    public function __construct(
        ?int $idDiagnostico,
        int $idUsuario,
        string $descricao,
        ?string $dataDiagnostico = null,
        ?string $categoria = null,
        ?string $perigo = null
    ) {
        $this->idDiagnostico = $idDiagnostico;
        $this->idUsuario = $idUsuario;
        $this->descricao = $descricao;
        $this->dataDiagnostico = $dataDiagnostico;
        $this->categoria = $categoria;
        $this->perigo = $perigo;
    }

    public function getIdDiagnostico(): ?int
    {
        return $this->idDiagnostico;
    }

    public function getIdUsuario(): int
    {
        return $this->idUsuario;
    }

    public function setIdUsuario(int $idUsuario): void
    {
        $this->idUsuario = $idUsuario;
    }

    public function getDescricao(): string
    {
        return $this->descricao;
    }

    public function setDescricao(string $descricao): void
    {
        $this->descricao = $descricao;
    }

    public function getDataDiagnostico(): ?string
    {
        return $this->dataDiagnostico;
    }

    public function getCategoria(): ?string
    {
        return $this->categoria;
    }

    public function getPerigo(): ?string
    {
        return $this->perigo;
    }
}
