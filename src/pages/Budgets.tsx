// pagina budgets - lista orçamentos
import { useEffect, useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useTranslation } from "react-i18next";
import { Sidebar } from "@/components/Sidebar";
import { Header } from "@/components/Header";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Plus, Eye, Pencil, Trash2, Filter } from "lucide-react";
import { toast } from "sonner";
import { BUDGETS_NS, ensureBudgetsI18n } from "@/lib/budgets-i18n";
import i18n from "@/lib/i18n";
import { ChevronLeft, ChevronRight } from "lucide-react";

interface Budget {
  id: string;
  name: string;
  client: string;
  createdAt: string; // dd/MM/yyyy
  totalValue: number; // cents or float
  status: "approved" | "pending" | "rejected" | "";
}

const formatCurrencyBRL = (value: number) => {
  try {
    return new Intl.NumberFormat("pt-BR", { style: "currency", currency: "BRL" }).format(value || 0);
  } catch {
    return `R$ ${(value || 0).toFixed(2)}`;
  }
};

const Budgets = () => {
  // garante que o namespace de orçamentos esteja carregado
  ensureBudgetsI18n();
  const { t } = useTranslation(BUDGETS_NS);
  const navigate = useNavigate();
  const [search, setSearch] = useState("");
  const [items, setItems] = useState<Budget[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const PAGE_SIZE = 20;
  const [page, setPage] = useState(1);
  const [confirmDelete, setConfirmDelete] = useState<{ id: string; name: string } | null>(null);
  const [isDeleting, setIsDeleting] = useState(false);

  useEffect(() => {
    const isAuthenticated = localStorage.getItem("isAuthenticated");
    if (!isAuthenticated) {
      navigate("/login");
      return;
    }

    setIsLoading(true);
    setError(null);
    const token = localStorage.getItem("userToken");
    const userId = localStorage.getItem("userId");
    const controller = new AbortController();

    const qs = userId ? `?usuario_id=${encodeURIComponent(userId)}` : "";
    fetch(`/appfacil/orcamentos.php${qs}`, {
      method: "GET",
      headers: token ? { Authorization: `Bearer ${token}` } : {},
      signal: controller.signal,
    })
      .then(async (response) => {
        if (!response.ok) {
          const text = await response.text().catch(() => "");
          throw new Error(`Falha HTTP ${response.status}. ${text || ""}`);
        }
        return response.json();
      })
      .then((data) => {
        let list: any[] = [];
        if (Array.isArray(data)) {
          list = data;
        } else if (Array.isArray((data as any)?.data)) {
          list = (data as any).data;
        } else if (Array.isArray((data as any)?.orcamentos)) {
          list = (data as any).orcamentos;
        }
        setItems(list.map((o, i) => normalizeBudget(o, i)));
      })
      .catch((err) => {
        console.error("Erro ao carregar orçamentos:", err);
        toast.error(t("error_loading"));
        setError(t("error_loading"));
      })
      .finally(() => setIsLoading(false));

    return () => controller.abort();
  }, [navigate]);

  const normalizeBudget = (o: any, i: number): Budget => {
    const rawStatus = String(o?.status ?? o?.situacao ?? "").toLowerCase();
    const mapStatus = (s: string): Budget["status"] => {
      if (["aprovado", "approved"].includes(s)) return "approved";
      if (["pendente", "pending"].includes(s)) return "pending";
      if (["rejeitado", "rejected"].includes(s)) return "rejected";
      return "";
    };

    // tenta escolher um nome amigável
    const name = o?.nome ?? o?.titulo ?? o?.observacoes ?? o?.obra_nome ?? `Orçamento ${o?.id ?? i + 1}`;
    const client = o?.cliente ?? o?.cliente_nome ?? o?.clienteId ?? "";
    const createdAtRaw = o?.data ?? o?.created_at ?? o?.criacao ?? "";
    const createdAt = formatDate(createdAtRaw);
    const totalValue = Number(o?.valor ?? o?.total ?? 0) || 0;

    return {
      id: String(o?.id ?? i + 1),
      name,
      client,
      createdAt,
      totalValue,
      status: mapStatus(rawStatus),
    };
  };

  const formatDate = (raw: string): string => {
    if (!raw) return "";
    // tenta formatos comuns: YYYY-MM-DD ou DD/MM/YYYY
    if (/^\d{4}-\d{2}-\d{2}/.test(raw)) {
      const [y, m, d] = raw.substring(0, 10).split("-");
      return `${d}/${m}/${y}`;
    }
    if (/^\d{2}\/\d{2}\/\d{4}/.test(raw)) return raw;
    return raw;
  };

  const filtered = useMemo(() => {
    const base = items;
    const term = search.trim().toLowerCase();
    if (!term) return base;
    return base.filter((b) =>
      [b.name, b.client].some((v) => String(v || "").toLowerCase().includes(term))
    );
  }, [items, search]);

  const totalPages = useMemo(() => {
    const n = Math.ceil(filtered.length / PAGE_SIZE);
    return Math.max(1, n || 1);
  }, [filtered.length]);

  const paginated = useMemo(() => {
    const start = (page - 1) * PAGE_SIZE;
    const end = start + PAGE_SIZE;
    return filtered.slice(start, end);
  }, [filtered, page]);

  // Resetar página ao mudar busca
  useEffect(() => {
    setPage(1);
  }, [search]);

  // Garantir página válida ao mudar quantidade
  useEffect(() => {
    if (page > totalPages) setPage(totalPages);
  }, [totalPages]);

  const statusBadge = (status: Budget["status"]) => {
    const map = {
      approved: { color: "bg-success", label: t("status.approved") },
      pending: { color: "bg-warning", label: t("status.pending") },
      rejected: { color: "bg-destructive", label: t("status.rejected") },
      "": { color: "bg-muted", label: "" },
    } as const;
    const cfg = map[status];
    return (
      <div className="flex items-center gap-2">
        <span className={`inline-block h-2 w-2 rounded-full ${cfg.color}`}></span>
        <span className="text-sm">{cfg.label}</span>
      </div>
    );
  };

  const deleteBudget = async () => {
    if (!confirmDelete) return;
    const usuario_id = localStorage.getItem("userId");
    if (!usuario_id) {
      toast.error("Usuário não identificado");
      return;
    }
    const token = localStorage.getItem("userToken");
    setIsDeleting(true);
    try {
      const res = await fetch("/appfacil/orcamentos.php", {
        method: "DELETE",
        headers: {
          "Content-Type": "application/json",
          ...(token ? { Authorization: `Bearer ${token}` } : {}),
        },
        body: JSON.stringify({ orcamento_id: confirmDelete.id, usuario_id }),
      });
      const json = await res.json().catch(() => ({ success: false }));
      if (!res.ok || !json?.success) {
        throw new Error(json?.message || `HTTP ${res.status}`);
      }
      toast.success("Orçamento excluído com sucesso");
      setItems((prev) => prev.filter((b) => b.id !== confirmDelete.id));
      setConfirmDelete(null);
    } catch (err) {
      console.error("Erro ao excluir orçamento", err);
      toast.error("Falha ao excluir orçamento");
    } finally {
      setIsDeleting(false);
    }
  };

  return (
    <div className="flex min-h-screen bg-background">
      <Sidebar />
      <div className="flex-1 flex flex-col">
        <Header />
        <main className="flex-1 p-8 overflow-y-auto">
          <div className="max-w-7xl mx-auto space-y-6">
            {/* Título e ações */}
            <div className="flex items-center justify-between">
              <h1 className="text-3xl font-bold text-foreground">{t("page_title")}</h1>
              <Button className="gap-2" onClick={() => navigate('/orcamentos/novo')}>
                <Plus className="h-4 w-4" />
                {t("add_button")}
              </Button>
            </div>

            {/* Barra de busca e filtro (placeholder) */}
            <div className="flex items-center gap-2">
              <div className="flex-1">
                <Input
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  placeholder={t("search_placeholder") || ""}
                />
              </div>
              <Button variant="outline" className="gap-2">
                <Filter className="h-4 w-4" />
                Filtro
              </Button>
            </div>

            {/* Tabela */}
            <div className="bg-card rounded-xl border border-border overflow-hidden">
              <div className="overflow-x-auto">
                <table className="min-w-full text-sm">
                  <thead className="bg-muted/40">
                    <tr>
                      <th className="px-4 py-3 text-left">
                        <input type="checkbox" aria-label="select-all" />
                      </th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">{t("columns.name")}</th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">{t("columns.client")}</th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">{t("columns.created_at")}</th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">{t("columns.total_value")}</th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">{t("columns.status")}</th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">{t("columns.actions")}</th>
                    </tr>
                  </thead>
                  <tbody>
                    {isLoading && (
                      <tr>
                        <td colSpan={7} className="px-4 py-6 text-center text-muted-foreground">
                          Carregando...
                        </td>
                      </tr>
                    )}
                    {!isLoading && filtered.length === 0 && (
                      <tr>
                        <td colSpan={7} className="px-4 py-6 text-center text-muted-foreground">
                          {t("empty")}
                        </td>
                      </tr>
                    )}
                    {!isLoading && paginated.map((b) => (
                      <tr key={b.id} className="border-t border-border/60 hover:bg-muted/20">
                        <td className="px-4 py-3">
                          <input type="checkbox" aria-label={`select-${b.id}`} />
                        </td>
                        <td className="px-4 py-3">
                          <div className="flex flex-col">
                            <span className="font-medium">{b.name}</span>
                          </div>
                        </td>
                        <td className="px-4 py-3">
                          <span className="text-foreground/90">{b.client || "-"}</span>
                        </td>
                        <td className="px-4 py-3">
                          <span className="text-foreground/90">{b.createdAt || "-"}</span>
                        </td>
                        <td className="px-4 py-3">
                          <span className="text-foreground/90">{formatCurrencyBRL(b.totalValue)}</span>
                        </td>
                        <td className="px-4 py-3">
                          {statusBadge(b.status)}
                        </td>
                        <td className="px-4 py-3">
                          <div className="flex items-center gap-3 text-muted-foreground">
                            <Eye
                              className="h-4 w-4 cursor-pointer hover:text-foreground"
                              onClick={() => navigate(`/orcamentos/ver/${b.id}`)}
                              title="Visualizar"
                            />
                            <Pencil
                              className="h-4 w-4 cursor-pointer hover:text-foreground"
                              onClick={() => navigate(`/orcamentos/editar/${b.id}`)}
                              title="Editar"
                            />
                            <Trash2
                              className="h-4 w-4 cursor-pointer hover:text-destructive"
                              onClick={() => setConfirmDelete({ id: b.id, name: b.name })}
                              title="Excluir"
                            />
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
              {/* Paginação: só mostra se houver mais de 20 registros */}
              {filtered.length > PAGE_SIZE && (
                <div className="p-4 border-t border-border flex items-center justify-center gap-3 text-sm">
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => setPage((p) => Math.max(1, p - 1))}
                    disabled={page === 1}
                    aria-label="Página anterior"
                  >
                    <ChevronLeft className="h-4 w-4" />
                    <span className="ml-1">Anterior</span>
                  </Button>
                  <span className="text-muted-foreground">
                    Página {page} de {totalPages}
                  </span>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
                    disabled={page === totalPages}
                    aria-label="Próxima página"
                  >
                    <span className="mr-1">Próximo</span>
                    <ChevronRight className="h-4 w-4" />
                  </Button>
                </div>
              )}
            </div>
            {/* Modal de confirmação de exclusão */}
            {confirmDelete && (
              <div className="fixed inset-0 z-50 flex items-center justify-center">
                <div className="absolute inset-0 bg-black/40" onClick={() => !isDeleting && setConfirmDelete(null)}></div>
                <div className="relative z-10 w-[90%] max-w-md rounded-xl border border-border bg-card p-6 shadow-lg">
                  <h3 className="text-lg font-semibold">Excluir orçamento</h3>
                  <p className="mt-2 text-sm text-muted-foreground">
                    Tem certeza que deseja excluir o orçamento
                    <span className="font-medium text-foreground"> “{confirmDelete.name}”</span>? Esta ação não pode ser desfeita.
                  </p>
                  <div className="mt-6 flex items-center justify-end gap-2">
                    <Button variant="outline" onClick={() => setConfirmDelete(null)} disabled={isDeleting}>
                      Cancelar
                    </Button>
                    <Button variant="destructive" onClick={deleteBudget} disabled={isDeleting}>
                      {isDeleting ? "Excluindo..." : "Excluir"}
                    </Button>
                  </div>
                </div>
              </div>
            )}
          </div>
        </main>
      </div>
    </div>
  );
};

export default Budgets;