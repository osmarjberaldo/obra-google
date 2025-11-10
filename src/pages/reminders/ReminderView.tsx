import { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { useTranslation } from "react-i18next";
import { Sidebar } from "@/components/Sidebar";
import { Header } from "@/components/Header";
import { Button } from "@/components/ui/button";
import { REMINDERS_NS, ensureRemindersI18n } from "@/lib/reminders-i18n";
import { toast } from "sonner";

const ReminderView = () => {
  ensureRemindersI18n();
  const { t } = useTranslation(REMINDERS_NS);
  const navigate = useNavigate();
  const { id } = useParams();

  const [dataObj, setDataObj] = useState<any | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const isAuthenticated = localStorage.getItem("isAuthenticated");
    if (!isAuthenticated) {
      navigate("/login");
      return;
    }

    const usuario_id = localStorage.getItem("userId");
    if (!usuario_id) {
      toast.error("Usuário não identificado");
      navigate("/lembretes");
      return;
    }
    const controller = new AbortController();
    const token = localStorage.getItem("userToken");
    setIsLoading(true);
    fetch(`/appfacil/lembretes.php?usuario_id=${encodeURIComponent(usuario_id)}&lembrete_id=${encodeURIComponent(id || "")}`, {
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
        const obj = Array.isArray(json) ? json[0] : (json?.data || json);
        if (!obj) throw new Error("Lembrete não encontrado");
        setDataObj(obj);
      })
      .catch((err) => {
        console.error("Erro ao carregar lembrete", err);
        toast.error(t("error_loading"));
      })
      .finally(() => setIsLoading(false));

    return () => controller.abort();
  }, [id, navigate, t]);

  const formatDate = (raw: string): string => {
    if (!raw) return "";
    if (/^\d{4}-\d{2}-\d{2}/.test(raw)) {
      const [y, m, d] = raw.substring(0, 10).split("-");
      return `${d}/${m}/${y}`;
    }
    if (/^\d{2}\/\d{2}\/\d{4}/.test(raw)) return raw;
    return raw;
  };

  return (
    <div className="flex min-h-screen bg-background">
      <Sidebar />
      <div className="flex-1 flex flex-col">
        <Header />
        <main className="flex-1 p-8 overflow-y-auto">
          <div className="max-w-3xl mx-auto space-y-6">
            <div className="flex items-center justify-between">
              <h1 className="text-3xl font-bold text-foreground">{t("view_title") || "Visualizar Lembrete"}</h1>
              <div className="flex items-center gap-2">
                <Button variant="outline" onClick={() => navigate('/lembretes')}>{t("actions.cancel") || "Voltar"}</Button>
                <Button onClick={() => navigate(`/lembretes/editar/${id}`)}>{t("edit_title") || "Editar Lembrete"}</Button>
              </div>
            </div>

            <div className="bg-card rounded-xl border border-border p-6 space-y-4">
              {isLoading && <div className="text-sm text-muted-foreground">Carregando...</div>}
              {!isLoading && !dataObj && <div className="text-sm text-destructive">{t("error_loading") || "Falha ao carregar lembrete"}</div>}
              {!isLoading && dataObj && (
                <div className="space-y-4">
                  <div>
                    <div className="text-sm text-muted-foreground mb-1">{t("fields.title") || "Título"}</div>
                    <div className="text-base">{dataObj.titulo || dataObj.title || "-"}</div>
                  </div>
                  <div>
                    <div className="text-sm text-muted-foreground mb-1">{t("fields.description") || "Descrição"}</div>
                    <div className="text-base whitespace-pre-wrap">{dataObj.descricao || "-"}</div>
                  </div>
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <div className="text-sm text-muted-foreground mb-1">{t("fields.date") || "Data"}</div>
                      <div className="text-base">{formatDate(dataObj.data_lembrete || dataObj.data || "")}</div>
                    </div>
                    <div>
                      <div className="text-sm text-muted-foreground mb-1">{t("fields.time") || "Hora"}</div>
                      <div className="text-base">{(dataObj.data_lembrete || "").substring(11,16) || "00:00"}</div>
                    </div>
                    <div>
                      <div className="text-sm text-muted-foreground mb-1">{t("fields.work") || "Obra"}</div>
                      <div className="text-base">{dataObj.obra_nome || dataObj.nome_obra || "-"}</div>
                    </div>
                  </div>
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <div className="text-sm text-muted-foreground mb-1">{t("fields.priority") || "Prioridade"}</div>
                      <div className="text-base">{dataObj.prioridade || "-"}</div>
                    </div>
                    <div>
                      <div className="text-sm text-muted-foreground mb-1">{t("fields.type") || "Tipo"}</div>
                      <div className="text-base">{dataObj.tipo || "-"}</div>
                    </div>
                    <div>
                      <div className="text-sm text-muted-foreground mb-1">Criado em</div>
                      <div className="text-base">{formatDate(dataObj.data_criacao || "")}</div>
                    </div>
                  </div>
                </div>
              )}
            </div>
          </div>
        </main>
      </div>
    </div>
  );
};

export default ReminderView;