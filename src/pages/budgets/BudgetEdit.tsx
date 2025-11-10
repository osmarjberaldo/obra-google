import { useEffect, useMemo, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { useTranslation } from "react-i18next";
import { Sidebar } from "@/components/Sidebar";
import { Header } from "@/components/Header";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { BUDGETS_NS, ensureBudgetsI18n } from "@/lib/budgets-i18n";
import { Plus, Trash2 } from "lucide-react";
import { toast } from "sonner";

type Item = {
  nome: string;
  descricao?: string;
  quantidade: number;
  valor: number; // unit value
};

type Work = {
  id: number;
  name: string;
};

const formatCurrencyBRL = (value: number) => {
  try {
    return new Intl.NumberFormat("pt-BR", { style: "currency", currency: "BRL" }).format(value || 0);
  } catch {
    return `R$ ${(value || 0).toFixed(2)}`;
  }
};

const onlyDigits = (s: string) => (s || "").replace(/\D/g, "");

const formatPhoneBR = (input: string) => {
  const d = onlyDigits(input).slice(0, 11);
  if (!d) return "";
  if (d.length <= 2) return `(${d}`;
  if (d.length <= 6) return `(${d.slice(0, 2)}) ${d.slice(2)}`;
  if (d.length <= 10) return `(${d.slice(0, 2)}) ${d.slice(2, 6)}-${d.slice(6)}`;
  return `(${d.slice(0, 2)}) ${d.slice(2, 7)}-${d.slice(7)}`;
};

const formatCpfCnpj = (input: string) => {
  const d = onlyDigits(input).slice(0, 14);
  if (!d) return "";
  if (d.length <= 11) {
    const p1 = d.slice(0, 3);
    const p2 = d.slice(3, 6);
    const p3 = d.slice(6, 9);
    const p4 = d.slice(9, 11);
    let out = p1;
    if (p2) out += `.${p2}`;
    if (p3) out += `.${p3}`;
    if (p4) out += `-${p4}`;
    return out;
  }
  const p1 = d.slice(0, 2);
  const p2 = d.slice(2, 5);
  const p3 = d.slice(5, 8);
  const p4 = d.slice(8, 12);
  const p5 = d.slice(12, 14);
  let out = p1;
  if (p2) out += `.${p2}`;
  if (p3) out += `.${p3}`;
  if (p4) out += `/${p4}`;
  if (p5) out += `-${p5}`;
  return out;
};

const formatCurrencyInputBRL = (input: string) => {
  const digits = onlyDigits(input);
  const value = Number(digits || "0") / 100;
  let display = "";
  try {
    display = new Intl.NumberFormat("pt-BR", { style: "currency", currency: "BRL" }).format(value);
  } catch {
    display = `R$ ${value.toFixed(2)}`;
  }
  return { display, value };
};

const BudgetEdit = () => {
  ensureBudgetsI18n();
  const { t } = useTranslation(BUDGETS_NS);
  const navigate = useNavigate();
  const { id } = useParams();

  const [cliente, setCliente] = useState("");
  const [cpfCnpj, setCpfCnpj] = useState("");
  const [telefone, setTelefone] = useState("");
  const [titulo, setTitulo] = useState("");
  const [escopo, setEscopo] = useState("");
  const [observacoes, setObservacoes] = useState("");
  const [itens, setItens] = useState<Item[]>([{ nome: "", quantidade: 1, valor: 0 }]);
  const [isSaving, setIsSaving] = useState(false);
  const [obras, setObras] = useState<Work[]>([]);
  const [obraId, setObraId] = useState<number | null>(null);
  const [valorOrcamento, setValorOrcamento] = useState<number>(0);
  const [valorOrcamentoStr, setValorOrcamentoStr] = useState<string>(() => formatCurrencyInputBRL("0").display);
  const [dataOrcamento, setDataOrcamento] = useState<string>(() => new Date().toISOString().slice(0, 10));
  const [validade, setValidade] = useState<string>("");
  const [status, setStatus] = useState<"pending" | "approved" | "rejected">("pending");

  useEffect(() => {
    const isAuthenticated = localStorage.getItem("isAuthenticated");
    if (!isAuthenticated) {
      navigate("/login");
    }
  }, [navigate]);

  const totalGeral = useMemo(() => itens.reduce((sum, i) => sum + (i.quantidade * i.valor), 0), [itens]);

  useEffect(() => {
    if (!valorOrcamento || valorOrcamento === 0) {
      const v = Number(totalGeral.toFixed(2));
      setValorOrcamento(v);
      setValorOrcamentoStr(formatCurrencyBRL(v));
    }
  }, [totalGeral]);

  // Carregar obras
  useEffect(() => {
    const usuario_id = localStorage.getItem("userId");
    if (!usuario_id) return;
    const token = localStorage.getItem("userToken");
    const url = `/appfacil/obras.php?usuario_id=${encodeURIComponent(usuario_id)}`;
    fetch(url, { headers: { ...(token ? { Authorization: `Bearer ${token}` } : {}) } })
      .then(async (res) => {
        const json = await res.json().catch(() => null);
        if (!res.ok || !json?.success) throw new Error(json?.message || `HTTP ${res.status}`);
        const data = (json.data || []) as any[];
        const works: Work[] = data.map((o) => ({ id: Number(o.id), name: String(o.name || o.nome_obra || "-") }));
        setObras(works);
      })
      .catch((err) => {
        console.error("Erro ao carregar obras", err);
        toast.error(t("form.toasts.saved_error"));
      });
  }, []);

  // Carregar orçamento existente
  useEffect(() => {
    const usuario_id = localStorage.getItem("userId");
    if (!usuario_id || !id) return;
    const token = localStorage.getItem("userToken");
    fetch(`/appfacil/orcamentos.php?usuario_id=${encodeURIComponent(usuario_id)}&orcamento_id=${encodeURIComponent(id)}`, {
      headers: { ...(token ? { Authorization: `Bearer ${token}` } : {}) },
    })
      .then(async (res) => {
        const json = await res.json().catch(() => null);
        if (!res.ok || !json?.success) throw new Error(json?.message || `HTTP ${res.status}`);
        const d = json.data as any;
        setCliente(String(d.cliente || ""));
        setCpfCnpj(String(d.cpf_cnpj || ""));
        setTelefone(String(d.telefone || ""));
        setTitulo(String(d.titulo || ""));
        setEscopo(String(d.escopo || ""));
        setObservacoes(String(d.observacoes || ""));
        setObraId(d.obra_id ? Number(d.obra_id) : null);
        const v = Number(d.valor || 0);
        setValorOrcamento(v);
        setValorOrcamentoStr(formatCurrencyBRL(v));
        setDataOrcamento(String(d.data || new Date().toISOString().slice(0, 10)));
        setValidade(String(d.validade || ""));
        const st = String(d.status || "pending").toLowerCase();
        setStatus((['approved','pending','rejected'] as const).includes(st as any) ? (st as any) : 'pending');
        const its: Item[] = Array.isArray(d.itens) ? d.itens.map((i: any) => ({
          nome: String(i.nome || ""),
          descricao: i.descricao || "",
          quantidade: Number(i.quantidade || 1),
          valor: Number(i.valor || 0),
        })) : [{ nome: "", quantidade: 1, valor: 0 }];
        setItens(its);
      })
      .catch((err) => {
        console.error("Erro ao carregar orçamento", err);
        toast.error(t("error_loading"));
      });
  }, [id]);

  const addItem = () => setItens((arr) => [...arr, { nome: "", quantidade: 1, valor: 0 }]);
  const removeItem = (index: number) => setItens((arr) => arr.filter((_, i) => i !== index));
  const updateItem = (index: number, patch: Partial<Item>) => {
    setItens((arr) => arr.map((it, i) => (i === index ? { ...it, ...patch } : it)));
  };

  const saveBudget = async () => {
    const phoneDigits = onlyDigits(telefone);
    const hasValue = (valorOrcamento ?? 0) > 0 || totalGeral > 0;
    if (!cliente || !titulo || !dataOrcamento || phoneDigits.length === 0 || !hasValue) {
      toast.error(t("form.toasts.missing_required"));
      return;
    }
    const usuario_id = localStorage.getItem("userId");
    if (!usuario_id || !id) {
      toast.error("Usuário não identificado");
      return;
    }

    setIsSaving(true);
    const token = localStorage.getItem("userToken");

    const body = {
      orcamento_id: Number(id),
      usuario_id,
      cliente,
      cpf_cnpj: cpfCnpj || null,
      telefone: telefone || null,
      titulo: titulo || null,
      escopo: escopo || null,
      valor: valorOrcamento || totalGeral,
      data: dataOrcamento,
      validade: validade || null,
      status: status,
      observacoes,
      obra_id: obraId || null,
      itens: itens.map((i) => ({
        nome: i.nome,
        descricao: i.descricao || undefined,
        quantidade: i.quantidade,
        valor: i.valor,
      })),
    };

    try {
      const res = await fetch("/appfacil/orcamentos.php", {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          ...(token ? { Authorization: `Bearer ${token}` } : {}),
        },
        body: JSON.stringify(body),
      });
      const json = await res.json().catch(() => ({ success: false }));
      if (!res.ok || !json?.success) {
        throw new Error(json?.message || `HTTP ${res.status}`);
      }
      toast.success(t("form.toasts.saved_success"));
      navigate("/orcamentos");
    } catch (err) {
      console.error("Erro ao atualizar orçamento", err);
      toast.error(t("form.toasts.saved_error"));
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
          <div className="max-w-7xl mx-auto space-y-6">
            <div className="flex items-center justify-between">
              <h1 className="text-3xl font-bold text-foreground">{t("form.page_title")}</h1>
            </div>

            {/* Informações do Cliente */}
            <section className="bg-card rounded-xl border border-border p-4 space-y-4">
              <h2 className="font-semibold text-foreground">{t("form.client_info")}</h2>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <label className="text-sm text-muted-foreground">{t("form.labels.client_name")}</label>
                  <Input value={cliente} required onChange={(e) => setCliente(e.target.value)} placeholder={t("form.labels.client_name") || ""} />
                </div>
                <div className="space-y-2">
                  <label className="text-sm text-muted-foreground">{t("form.labels.phone")}</label>
                  <Input value={telefone} required onChange={(e) => setTelefone(formatPhoneBR(e.target.value))} placeholder={t("form.labels.phone") || ""} inputMode="tel" />
                </div>
                <div className="space-y-2 md:col-span-2">
                  <label className="text-sm text-muted-foreground">{t("form.labels.cpf_cnpj")}</label>
                  <Input value={cpfCnpj} onChange={(e) => setCpfCnpj(formatCpfCnpj(e.target.value))} placeholder={t("form.labels.cpf_cnpj") || ""} inputMode="numeric" />
                </div>
              </div>
            </section>

            {/* Informações do Orçamento */}
            <section className="bg-card rounded-xl border border-border p-4 space-y-4">
              <h2 className="font-semibold text-foreground">{t("form.labels.budget_info")}</h2>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div className="space-y-2">
                  <label className="text-sm text-muted-foreground">{t("form.labels.work")}</label>
                  <select
                    className="w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                    value={obraId ?? ""}
                    onChange={(e) => setObraId(e.target.value ? Number(e.target.value) : null)}
                  >
                    <option value="">{t("form.placeholders.work_select")}</option>
                    {obras.map((o) => (
                      <option key={o.id} value={o.id}>{o.name}</option>
                    ))}
                  </select>
                </div>
                <div className="space-y-2">
                  <label className="text-sm text-muted-foreground">{t("form.labels.budget_date")}</label>
                  <Input type="date" value={dataOrcamento} required onChange={(e) => setDataOrcamento(e.target.value)} />
                </div>
                <div className="space-y-2 md:col-span-3">
                  <label className="text-sm text-muted-foreground">{t("form.labels.budget_value")}</label>
                  <Input
                    value={valorOrcamentoStr}
                    onChange={(e) => {
                      const { display, value } = formatCurrencyInputBRL(e.target.value);
                      setValorOrcamento(value);
                      setValorOrcamentoStr(display);
                    }}
                    required
                    inputMode="numeric"
                  />
                </div>
                <div className="space-y-2">
                  <label className="text-sm text-muted-foreground">{t("form.labels.status")}</label>
                  <select
                    className="w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                    value={status}
                    onChange={(e) => setStatus(e.target.value as "pending" | "approved" | "rejected")}
                  >
                    <option value="pending">{t("status.pending")}</option>
                    <option value="approved">{t("status.approved")}</option>
                    <option value="rejected">{t("status.rejected")}</option>
                  </select>
                </div>
                <div className="space-y-2">
                  <label className="text-sm text-muted-foreground">{t("form.labels.valid_until")}</label>
                  <Input type="date" value={validade} onChange={(e) => setValidade(e.target.value)} />
                </div>
              </div>
            </section>

            {/* Descrição dos Serviços */}
            <section className="bg-card rounded-xl border border-border p-4 space-y-4">
              <h2 className="font-semibold text-foreground">{t("form.labels.budget_title")}</h2>
              <Input value={titulo} required onChange={(e) => setTitulo(e.target.value)} placeholder="Ex: Reforma da Cozinha - Apartamento 101" />
              <div className="space-y-2">
                <label className="text-sm text-muted-foreground">{t("form.labels.work_scope")}</label>
                <Textarea value={escopo} onChange={(e) => setEscopo(e.target.value)} placeholder="Descreva os serviços a serem realizados..." rows={4} />
              </div>
            </section>

            {/* Itens do Orçamento */}
            <section className="bg-card rounded-xl border border-border p-4 space-y-4">
              <div className="flex items-center justify-between">
                <h2 className="font-semibold text-foreground">{t("form.labels.items")}</h2>
                <Button variant="outline" className="gap-2" onClick={addItem}>
                  <Plus className="h-4 w-4" />
                  {t("form.actions.add_item")}
                </Button>
              </div>

              <div className="overflow-x-auto">
                <table className="min-w-full text-sm">
                  <thead className="bg-muted/40">
                    <tr>
                      <th className="px-4 py-3 text-left w-[40%]">{t("form.labels.item_description")}</th>
                      <th className="px-4 py-3 text-left w-[10%]">{t("form.labels.qty")}</th>
                      <th className="px-4 py-3 text-left w-[20%]">{t("form.labels.unit_value")}</th>
                      <th className="px-4 py-3 text-left w-[20%]">{t("form.labels.total_value")}</th>
                      <th className="px-4 py-3 w-[10%]"></th>
                    </tr>
                  </thead>
                  <tbody>
                    {itens.map((it, idx) => {
                      const total = (it.quantidade || 0) * (it.valor || 0);
                      return (
                        <tr key={idx} className="border-t border-border/60">
                          <td className="px-4 py-2">
                            <Input value={it.nome} onChange={(e) => updateItem(idx, { nome: e.target.value })} />
                          </td>
                          <td className="px-4 py-2">
                            <Input type="number" min={0} value={it.quantidade} onChange={(e) => updateItem(idx, { quantidade: Number(e.target.value) })} />
                          </td>
                          <td className="px-4 py-2">
                            <Input type="number" min={0} step="0.01" value={it.valor} onChange={(e) => updateItem(idx, { valor: Number(e.target.value) })} />
                          </td>
                          <td className="px-4 py-2">{formatCurrencyBRL(total)}</td>
                          <td className="px-4 py-2 text-right">
                            <Button variant="ghost" size="sm" onClick={() => removeItem(idx)}>
                              <Trash2 className="h-4 w-4" />
                            </Button>
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>

              <div className="flex justify-end items-center gap-4 pt-2">
                <span className="text-sm text-muted-foreground">{t("form.total_label")}</span>
                <span className="text-lg font-semibold">{formatCurrencyBRL(totalGeral)}</span>
              </div>
            </section>

            {/* Observações e Condições */}
            <section className="bg-card rounded-xl border border-border p-4 space-y-4">
              <h2 className="font-semibold text-foreground">{t("form.labels.notes_conditions")}</h2>
              <Textarea value={observacoes} onChange={(e) => setObservacoes(e.target.value)} placeholder="Ex: validade do orçamento, formas de pagamento..." rows={4} />
            </section>

            {/* Ações */}
            <div className="flex items-center justify-end gap-2">
              <Button variant="outline" onClick={() => navigate("/orcamentos")}>{t("form.actions.cancel")}</Button>
              <Button onClick={saveBudget} disabled={isSaving}>{t("form.actions.save")}</Button>
            </div>
          </div>
        </main>
      </div>
    </div>
  );
};

export default BudgetEdit;