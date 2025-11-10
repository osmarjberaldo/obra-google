import { useEffect, useMemo, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { useTranslation } from "react-i18next";
import { Sidebar } from "@/components/Sidebar";
import { Header } from "@/components/Header";
import { Button } from "@/components/ui/button";
import { BUDGETS_NS, ensureBudgetsI18n } from "@/lib/budgets-i18n";
import { toast } from "sonner";

type Item = {
  id?: number;
  nome: string;
  descricao?: string;
  quantidade: number;
  valor: number; // unit value
};

type Budget = {
  id: number;
  cliente: string;
  cpf_cnpj?: string | null;
  telefone?: string | null;
  valor: number;
  titulo?: string | null;
  escopo?: string | null;
  data: string;
  validade?: string | null;
  status: string;
  observacoes?: string | null;
  obra_id?: number | null;
  obra_nome?: string | null;
  itens: Item[];
};

const formatCurrencyBRL = (value: number) => {
  try {
    return new Intl.NumberFormat("pt-BR", { style: "currency", currency: "BRL" }).format(value || 0);
  } catch {
    return `R$ ${(value || 0).toFixed(2)}`;
  }
};

const BudgetView = () => {
  ensureBudgetsI18n();
  const { t } = useTranslation(BUDGETS_NS);
  const navigate = useNavigate();
  const { id } = useParams();

  const [budget, setBudget] = useState<Budget | null>(null);
  const [isLoading, setIsLoading] = useState(false);

  useEffect(() => {
    const isAuthenticated = localStorage.getItem("isAuthenticated");
    if (!isAuthenticated) {
      navigate("/login");
    }
  }, [navigate]);

  useEffect(() => {
    const usuario_id = localStorage.getItem("userId");
    if (!usuario_id || !id) return;
    const token = localStorage.getItem("userToken");
    setIsLoading(true);
    fetch(`/appfacil/orcamentos.php?usuario_id=${encodeURIComponent(usuario_id)}&orcamento_id=${encodeURIComponent(id)}`, {
      headers: {
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
    })
      .then(async (res) => {
        const json = await res.json().catch(() => null);
        if (!res.ok || !json?.success) throw new Error(json?.message || `HTTP ${res.status}`);
        const data = json.data as any;
        const b: Budget = {
          id: Number(data.id),
          cliente: String(data.cliente || ""),
          cpf_cnpj: data.cpf_cnpj || null,
          telefone: data.telefone || null,
          valor: Number(data.valor || 0),
          titulo: data.titulo || "",
          escopo: data.escopo || "",
          data: data.data || "",
          validade: data.validade || null,
          status: data.status || "",
          observacoes: data.observacoes || "",
          obra_id: data.obra_id ? Number(data.obra_id) : null,
          obra_nome: data.obra_nome || "",
          itens: Array.isArray(data.itens)
            ? data.itens.map((i: any) => ({
                id: Number(i.id),
                nome: String(i.nome || ""),
                descricao: i.descricao || "",
                quantidade: Number(i.quantidade || 0),
                valor: Number(i.valor || 0),
              }))
            : [],
        };
        setBudget(b);
      })
      .catch((err) => {
        console.error("Erro ao carregar orçamento", err);
        toast.error(t("error_loading"));
      })
      .finally(() => setIsLoading(false));
  }, [id]);

  const totalGeral = useMemo(() => {
    if (!budget) return 0;
    const itens = budget.itens || [];
    const sumItens = itens.reduce((sum, i) => sum + (i.quantidade * i.valor), 0);
    return itens.length > 0 ? sumItens : Number(budget.valor || 0);
  }, [budget]);

  const statusLabel = useMemo(() => {
    const raw = (budget?.status || "").toLowerCase().trim().replace(/\s+/g, "_");
    // tenta traduzir pelo grupo status; se não existir, cai no valor bruto
    return t(`status.${raw}`, { defaultValue: budget?.status || "" }) as string;
  }, [budget, t]);

  return (
    <div className="flex min-h-screen bg-background">
      {/* Ocultar navegação ao imprimir */}
      <div className="print:hidden">
        <Sidebar />
      </div>
      <div className="flex-1 flex flex-col">
        <div className="print:hidden">
          <Header />
        </div>
        <main className="flex-1 p-8 overflow-y-auto">
          <div className="max-w-4xl mx-auto space-y-6 bg-white shadow-sm rounded-xl border border-border p-6">
            {/* Ações superiores (ocultas na impressão) */}
            <div className="flex items-center justify-between print:hidden">
              <Button variant="outline" onClick={() => navigate(-1)}>
                {t("view.back")}
              </Button>
              <div className="flex items-center gap-2">
                <Button variant="outline" onClick={() => window.print()}>
                  {t("view.print")}
                </Button>
                {budget && (
                  <Button onClick={() => navigate(`/orcamentos/editar/${budget.id}`)}>
                    {t("view.edit")}
                  </Button>
                )}
              </div>
            </div>

            {isLoading && (
              <div className="text-center text-muted-foreground">{t("view.loading")}</div>
            )}
            {!isLoading && budget && (
              <div className="space-y-6">
                {/* Cabeçalho do orçamento */}
                <div className="text-center">
                  <h1 className="text-2xl font-bold">{budget.titulo || (t("view.title_fallback") as string)}</h1>
                  <p className="text-sm text-muted-foreground">
                    {t("view.date")}: {budget.data}
                  </p>
                </div>

                {/* Informações do cliente e obra */}
                <section className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <h2 className="font-semibold">{t("view.client_section")}</h2>
                    <p>{budget.cliente}</p>
                    {budget.telefone && (
                      <p>
                        {t("view.phone")}: {budget.telefone}
                      </p>
                    )}
                    {budget.cpf_cnpj && (
                      <p>
                        {t("view.cpf_cnpj")}: {budget.cpf_cnpj}
                      </p>
                    )}
                  </div>
                  <div>
                    {budget.obra_nome && (
                      <h2 className="font-semibold">{t("view.work_section")}</h2>
                    )}
                    {budget.obra_nome && <p>{budget.obra_nome}</p>}
                    {budget.validade && (
                      <p>
                        {t("view.valid_until")}: {budget.validade}
                      </p>
                    )}
                    <p>
                      {t("view.status")}: {statusLabel}
                    </p>
                  </div>
                </section>

                {/* Itens e total */}
                <section>
                  <h2 className="font-semibold mb-2">{t("view.items_section")}</h2>
                  <table className="min-w-full text-sm">
                    <thead>
                      <tr>
                        <th className="text-left py-2">{t("view.description")}</th>
                        <th className="text-left py-2">{t("view.qty")}</th>
                        <th className="text-left py-2">{t("view.unit_value")}</th>
                        <th className="text-left py-2">{t("view.total")}</th>
                      </tr>
                    </thead>
                    <tbody>
                      {(budget.itens || []).length === 0 && (
                        <tr>
                          <td className="py-2 text-muted-foreground" colSpan={4}>
                            {t("view.empty_items")}
                          </td>
                        </tr>
                      )}
                      {(budget.itens || []).map((it, idx) => (
                        <tr key={idx} className="border-t">
                          <td className="py-2">{it.nome}</td>
                          <td className="py-2">{it.quantidade}</td>
                          <td className="py-2">{formatCurrencyBRL(it.valor)}</td>
                          <td className="py-2">{formatCurrencyBRL((it.quantidade || 0) * (it.valor || 0))}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                  <div className="flex justify-end items-center gap-4 pt-4">
                    <span className="text-sm text-muted-foreground">{t("view.grand_total")}</span>
                    <span className="text-lg font-semibold">{formatCurrencyBRL(totalGeral)}</span>
                  </div>
                </section>

                {/* Escopo e observações */}
                {budget.escopo && (
                  <section>
                    <h2 className="font-semibold">{t("view.scope_section")}</h2>
                    <p className="whitespace-pre-line">{budget.escopo}</p>
                  </section>
                )}
                {budget.observacoes && (
                  <section>
                    <h2 className="font-semibold">{t("view.observations_section")}</h2>
                    <p className="whitespace-pre-line">{budget.observacoes}</p>
                  </section>
                )}
              </div>
            )}
          </div>
        </main>
      </div>
    </div>
  );
};

export default BudgetView;