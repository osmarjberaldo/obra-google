// pagina reminders - lista de lembretes
import { useEffect, useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useTranslation } from "react-i18next";
import { Sidebar } from "@/components/Sidebar";
import { Header } from "@/components/Header";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Eye, Pencil, Trash2, Filter, Plus } from "lucide-react";
import {
  AlertDialog,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogCancel,
  AlertDialogAction,
} from "@/components/ui/alert-dialog";
import { toast } from "sonner";
import { REMINDERS_NS, ensureRemindersI18n } from "@/lib/reminders-i18n";

interface Reminder {
  id: string;
  title: string;
  work: string;
  obraId?: string;
  createdAt: string; // dd/MM/yyyy
  dueDate: string; // dd/MM/yyyy
  status: "pending" | "done" | "overdue" | "canceled" | "";
  statusText?: string;
}

const Reminders = () => {
  // garante que o namespace de lembretes esteja carregado
  ensureRemindersI18n();
  const { t } = useTranslation(REMINDERS_NS);
  const navigate = useNavigate();
  const [search, setSearch] = useState("");
  const [items, setItems] = useState<Reminder[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const PAGE_SIZE = 20;
  const [page, setPage] = useState(1);
  const [confirmOpen, setConfirmOpen] = useState(false);
  const [deleteId, setDeleteId] = useState<string | null>(null);
  const [obrasMap, setObrasMap] = useState<Record<string, string>>({});

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
    fetch(`/appfacil/lembretes.php${qs}`, {
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
        } else if (Array.isArray((data as any)?.lembretes)) {
          list = (data as any).lembretes;
        } else if ((data as any)?.data && typeof (data as any).data === "object") {
          // quando API retorna um único objeto em data, normalizamos para lista de um
          list = [(data as any).data];
        }
        setItems(list.map((o, i) => normalizeReminder(o, i)));
      })
      .catch((err) => {
        console.error("Erro ao carregar lembretes:", err);
        toast.error(t("error_loading"));
        setError(t("error_loading"));
      })
      .finally(() => setIsLoading(false));

    return () => controller.abort();
  }, [navigate]);

  // Carrega lista de obras para mapear ID -> nome
  useEffect(() => {
    const usuario_id = localStorage.getItem("userId");
    if (!usuario_id) return;
    const controller = new AbortController();
    const token = localStorage.getItem("userToken");
    fetch(`/appfacil/obras.php?usuario_id=${encodeURIComponent(usuario_id)}`, {
      method: "GET",
      headers: token ? { Authorization: `Bearer ${token}` } : {},
      signal: controller.signal,
    })
      .then(async (res) => {
        if (!res.ok) {
          const text = await res.text().catch(() => "");
          throw new Error(`Falha HTTP ${res.status}. ${text || ""}`);
        }
        return res.json();
      })
      .then((json) => {
        const list = Array.isArray(json) ? json : (json?.data || []);
        const map: Record<string, string> = {};
        for (const o of list) {
          const id = String(o?.id ?? "");
          if (!id) continue;
          map[id] = o?.name || o?.nome_obra || `Obra ${id}`;
        }
        setObrasMap(map);
      })
      .catch((err) => {
        console.error("Erro ao carregar obras", err);
        // não bloqueia a UI se falhar
      });

    return () => controller.abort();
  }, []);

  const normalizeReminder = (o: any, i: number): Reminder => {
    const rawStatus = String(o?.status ?? o?.situacao ?? o?.state ?? "").toLowerCase();
    const mapStatus = (s: string): Reminder["status"] => {
      if (["pendente", "pending", "em_andamento", "andamento"].includes(s)) return "pending";
      if (["concluido", "concluida", "concluded", "done", "completed"].includes(s)) return "done";
      if (["atrasado", "atrasada", "overdue", "vencido", "vencida"].includes(s)) return "overdue";
      if (["cancelado", "cancelada", "canceled"].includes(s)) return "canceled";
      return "";
    };

    const title = o?.titulo ?? o?.title ?? o?.nome ?? `Lembrete ${o?.id ?? i + 1}`;
    const work = o?.obra_nome ?? o?.nome_obra ?? o?.obra ?? o?.work ?? "";
    const createdAt = formatDate(o?.data_criacao ?? o?.data ?? o?.created_at ?? o?.criacao ?? "");
    const dueDate = formatDate(o?.data_lembrete ?? o?.validade ?? o?.due_date ?? o?.vence_em ?? "");

    return {
      id: String(o?.id ?? o?.lembrete_id ?? i + 1),
      title,
      work,
      obraId: o?.obra_id != null ? String(o.obra_id) : undefined,
      createdAt,
      dueDate,
      status: mapStatus(rawStatus),
      statusText: o?.status ? String(o.status) : undefined,
    };
  };

  const formatDate = (raw: string): string => {
    if (!raw) return "";
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
    return base.filter((r) =>
      [r.title, r.work].some((v) => String(v || "").toLowerCase().includes(term))
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

  useEffect(() => { setPage(1); }, [search]);
  useEffect(() => { if (page > totalPages) setPage(totalPages); }, [totalPages]);

  const statusBadge = (status: Reminder["status"], text?: string) => {
    const map = {
      pending: { color: "bg-warning", label: t("status.pending") },
      done: { color: "bg-success", label: t("status.done") },
      overdue: { color: "bg-destructive", label: t("status.overdue") },
      canceled: { color: "bg-muted", label: t("status.canceled") },
      "": { color: "bg-muted", label: "" },
    } as const;
    const cfg = map[status];
    return (
      <div className="flex items-center gap-2">
        <span className={`inline-block h-2 w-2 rounded-full ${cfg.color}`}></span>
        <span className="text-sm">{cfg.label || text || "-"}</span>
      </div>
    );
  };

  const deleteReminder = async (reminderId: string) => {
    const usuario_id = localStorage.getItem("userId");
    if (!usuario_id) {
      toast.error("Usuário não identificado");
      return;
    }
    const token = localStorage.getItem("userToken");
    try {
      const res = await fetch("/appfacil/lembretes.php", {
        method: "DELETE",
        headers: {
          "Content-Type": "application/json",
          ...(token ? { Authorization: `Bearer ${token}` } : {}),
        },
        body: JSON.stringify({ id: reminderId, usuario_id }),
      });
      const json = await res.json().catch(() => ({ success: false }));
      if (!res.ok || !json?.success) {
        throw new Error(json?.message || `HTTP ${res.status}`);
      }
      toast.success("Lembrete excluído com sucesso");
      setItems((prev) => prev.filter((r) => r.id !== reminderId));
    } catch (err) {
      console.error("Falha ao excluir lembrete", err);
      toast.error("Falha ao excluir lembrete");
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
              <Button className="gap-2" onClick={() => navigate('/lembretes/novo')}>
                <Plus className="h-4 w-4" />
                {t("add_button") || "Novo Lembrete"}
              </Button>
            </div>

            {/* Barra de busca */}
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
                      <th className="px-4 py-3 text-left"><input type="checkbox" aria-label="select-all" /></th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">{t("columns.title")}</th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">{t("columns.work")}</th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">{t("columns.created_at")}</th>
                      <th className="px-4 py-3 text-left font-medium text-muted-foreground">{t("columns.due_date")}</th>
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
                    {!isLoading && paginated.map((r) => (
                      <tr key={r.id} className="border-t border-border/60 hover:bg-muted/20">
                        <td className="px-4 py-3">
                          <input type="checkbox" aria-label={`select-${r.id}`} />
                        </td>
                        <td className="px-4 py-3">
                          <div className="flex flex-col">
                            <span className="font-medium">{r.title}</span>
                          </div>
                        </td>
                        <td className="px-4 py-3">
                          <span className="text-foreground/90">{r.work || (r.obraId ? (obrasMap[r.obraId] || "-") : "-")}</span>
                        </td>
                        <td className="px-4 py-3">
                          <span className="text-foreground/90">{r.createdAt || "-"}</span>
                        </td>
                        <td className="px-4 py-3">
                          <span className="text-foreground/90">{r.dueDate || "-"}</span>
                        </td>
                        <td className="px-4 py-3">{statusBadge(r.status, r.statusText)}</td>
                        <td className="px-4 py-3">
                          <div className="flex items-center gap-3 text-muted-foreground">
                            <Eye
                              className="h-4 w-4 cursor-pointer hover:text-foreground"
                              onClick={() => navigate(`/lembretes/ver/${r.id}`)}
                              title="Visualizar"
                            />
                            <Pencil
                              className="h-4 w-4 cursor-pointer hover:text-foreground"
                              onClick={() => navigate(`/lembretes/editar/${r.id}`)}
                              title="Editar"
                            />
                            <Trash2
                              className="h-4 w-4 cursor-pointer hover:text-destructive"
                              onClick={() => { setDeleteId(r.id); setConfirmOpen(true); }}
                              title="Excluir"
                            />
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
              {filtered.length > PAGE_SIZE && (
                <div className="p-4 border-t border-border text-sm text-center">Página {page}</div>
              )}
            </div>
            {/* Modal de confirmação de exclusão */}
            <AlertDialog open={confirmOpen} onOpenChange={setConfirmOpen}>
              <AlertDialogContent>
                <AlertDialogHeader>
                  <AlertDialogTitle>{t("delete_confirm_title") || "Excluir lembrete"}</AlertDialogTitle>
                  <AlertDialogDescription>
                    {t("delete_confirm_description") || "Tem certeza que deseja excluir este lembrete? Esta ação não pode ser desfeita."}
                  </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                  <AlertDialogCancel>{t("actions.cancel") || "Cancelar"}</AlertDialogCancel>
                  <AlertDialogAction
                    onClick={() => {
                      if (deleteId) deleteReminder(deleteId);
                      setConfirmOpen(false);
                      setDeleteId(null);
                    }}
                  >
                    {t("delete_confirm_ok") || "Excluir"}
                  </AlertDialogAction>
                </AlertDialogFooter>
              </AlertDialogContent>
            </AlertDialog>
          </div>
        </main>
      </div>
    </div>
  );
};

export default Reminders;