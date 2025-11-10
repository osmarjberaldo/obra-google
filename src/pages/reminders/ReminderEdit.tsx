import { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { useTranslation } from "react-i18next";
import { Sidebar } from "@/components/Sidebar";
import { Header } from "@/components/Header";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { REMINDERS_NS, ensureRemindersI18n } from "@/lib/reminders-i18n";
import { toast } from "sonner";

const ReminderEdit = () => {
  ensureRemindersI18n();
  const { t } = useTranslation(REMINDERS_NS);
  const navigate = useNavigate();
  const { id } = useParams();

  const [titulo, setTitulo] = useState("");
  const [descricao, setDescricao] = useState("");
  const [data, setData] = useState(""); // YYYY-MM-DD
  const [hora, setHora] = useState(""); // HH:MM
  const [prioridade, setPrioridade] = useState("media");
  const [tipo, setTipo] = useState("geral");
  const [obraId, setObraId] = useState<string>("");
  const [obras, setObras] = useState<{ id: string; name: string }[]>([]);
  const [isObrasLoading, setIsObrasLoading] = useState(false);
  const [isSaving, setIsSaving] = useState(false);
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
        const dataObj = Array.isArray(json) ? json[0] : (json?.data || json);
        if (!dataObj) throw new Error("Lembrete não encontrado");
        // campos podem variar, tentamos mapear
        setTitulo(dataObj.titulo || dataObj.title || "");
        setDescricao(dataObj.descricao || "");
        const rawDate: string = dataObj.data_lembrete || dataObj.data || "";
        if (/^\d{4}-\d{2}-\d{2}/.test(rawDate)) {
          const [y, m, d] = rawDate.substring(0,10).split("-");
          setData(`${y}-${m}-${d}`);
          const hhmmss = rawDate.substring(11,19) || "00:00:00";
          setHora(hhmmss.substring(0,5));
        } else if (/^\d{2}\/\d{2}\/\d{4}/.test(rawDate)) {
          const [d, m, y] = rawDate.substring(0,10).split("/");
          setData(`${y}-${m}-${d}`);
          setHora("00:00");
        }
        setPrioridade(dataObj.prioridade || "media");
        setTipo(dataObj.tipo || "geral");
        setObraId(dataObj.obra_id ? String(dataObj.obra_id) : "");
      })
      .catch((err) => {
        console.error("Erro ao carregar lembrete", err);
        toast.error(t("error_loading"));
      })
      .finally(() => setIsLoading(false));

    return () => controller.abort();
  }, [id, navigate, t]);

  useEffect(() => {
    const usuario_id = localStorage.getItem("userId");
    if (!usuario_id) return;
    const controller = new AbortController();
    const token = localStorage.getItem("userToken");
    setIsObrasLoading(true);
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
        const options = list.map((o: any) => ({ id: String(o.id), name: o.name || o.nome_obra || `Obra ${o.id}` }));
        setObras(options);
      })
      .catch((err) => {
        console.error("Erro ao carregar obras", err);
        toast.error("Falha ao carregar obras");
      })
      .finally(() => setIsObrasLoading(false));

    return () => controller.abort();
  }, []);

  const formatDateTime = () => {
    if (!data) return "";
    const hhmm = hora || "00:00";
    return `${data} ${hhmm}:00`;
  };

  const save = async () => {
    const usuario_id = localStorage.getItem("userId");
    if (!usuario_id) {
      toast.error("Usuário não identificado");
      return;
    }
    if (!id || !titulo || !data) {
      toast.error("Preencha ID, título e data do lembrete");
      return;
    }
    setIsSaving(true);
    const token = localStorage.getItem("userToken");
    try {
      const res = await fetch("/appfacil/lembretes.php", {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          ...(token ? { Authorization: `Bearer ${token}` } : {}),
        },
        body: JSON.stringify({
          id,
          usuario_id,
          titulo,
          descricao: descricao || null,
          data_lembrete: formatDateTime(),
          prioridade,
          tipo,
          obra_id: obraId ? Number(obraId) : null,
        }),
      });
      const json = await res.json().catch(() => ({ success: false }));
      if (!res.ok || !json?.success) {
        throw new Error(json?.message || `HTTP ${res.status}`);
      }
      toast.success("Lembrete atualizado com sucesso");
      navigate("/lembretes");
    } catch (err) {
      console.error("Falha ao atualizar lembrete", err);
      toast.error("Falha ao atualizar lembrete");
    } finally {
      setIsSaving(false);
    }
  };

  return (
    <div className="flex min-h-screen bg-background">
      <Sidebar />
      <div className="flex-1 flex flex-col">
        <Header />
        <main className="flex-1 p-8 overflow-y-auto">
          <div className="max-w-3xl mx-auto space-y-6">
            <div className="flex items-center justify-between">
              <h1 className="text-3xl font-bold text-foreground">{t("edit_title") || "Editar Lembrete"}</h1>
              <div className="flex items-center gap-2">
                <Button variant="outline" onClick={() => navigate('/lembretes')}>{t("actions.cancel") || "Cancelar"}</Button>
                <Button onClick={save} disabled={isSaving || isLoading}>{isSaving ? "Salvando..." : (t("actions.save") || "Salvar")}</Button>
              </div>
            </div>

            <div className="bg-card rounded-xl border border-border p-6 space-y-4">
              <div>
                <label className="block text-sm text-muted-foreground mb-1">{t("fields.title") || "Título"}</label>
                <Input value={titulo} onChange={(e) => setTitulo(e.target.value)} placeholder={t("fields.title") || "Título"} />
              </div>
              <div>
                <label className="block text-sm text-muted-foreground mb-1">{t("fields.description") || "Descrição"}</label>
                <textarea
                  value={descricao}
                  onChange={(e) => setDescricao(e.target.value)}
                  className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                  rows={4}
                  placeholder={t("fields.description") || "Descrição"}
                />
              </div>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm text-muted-foreground mb-1">{t("fields.date") || "Data"}</label>
                  <Input type="date" value={data} onChange={(e) => setData(e.target.value)} />
                </div>
                <div>
                  <label className="block text-sm text-muted-foreground mb-1">{t("fields.time") || "Hora"}</label>
                  <Input type="time" value={hora} onChange={(e) => setHora(e.target.value)} />
                </div>
              </div>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label className="block text-sm text-muted-foreground mb-1">{t("fields.priority") || "Prioridade"}</label>
                  <select value={prioridade} onChange={(e) => setPrioridade(e.target.value)} className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                    <option value="baixa">{t("priority.low") || "Baixa"}</option>
                    <option value="media">{t("priority.medium") || "Média"}</option>
                    <option value="alta">{t("priority.high") || "Alta"}</option>
                  </select>
                </div>
                <div>
                  <label className="block text-sm text-muted-foreground mb-1">{t("fields.type") || "Tipo"}</label>
                  <select value={tipo} onChange={(e) => setTipo(e.target.value)} className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                    <option value="geral">{t("type.general") || "Geral"}</option>
                    <option value="obra">{t("type.work") || "Obra"}</option>
                    <option value="financeiro">{t("type.financial") || "Financeiro"}</option>
                    <option value="reuniao">{t("type.meeting") || "Reunião"}</option>
                    <option value="prazo">{t("type.deadline") || "Prazo"}</option>
                  </select>
                </div>
                <div>
                  <label className="block text-sm text-muted-foreground mb-1">{t("fields.work") || "Obra"}</label>
                  <select
                    value={obraId}
                    onChange={(e) => setObraId(e.target.value)}
                    className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                  >
                    <option value="">{isObrasLoading ? "Carregando obras..." : (t("select_work_placeholder") || "Selecione a obra (opcional)")}</option>
                    {obras.map((o) => (
                      <option key={o.id} value={o.id}>{o.name}</option>
                    ))}
                  </select>
                </div>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  );
};

export default ReminderEdit;