import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import { LanguageProvider } from "./contexts/LanguageContext";
import { useEffect, useState } from "react";
import { initI18n } from "./lib/i18n";
import Login from "./pages/Login";
import Signup from "./pages/Signup";
import ForgotPassword from "./pages/ForgotPassword";
import Dashboard from "./pages/Dashboard";
import Reports from "./pages/Reports";
import Financial from "./pages/Financial";
import NotFound from "./pages/NotFound";
import Budgets from "./pages/Budgets";
import BudgetNew from "./pages/budgets/BudgetNew";
import BudgetView from "./pages/budgets/BudgetView";
import BudgetEdit from "./pages/budgets/BudgetEdit";
import Reminders from "./pages/Reminders";
import ReminderNew from "./pages/reminders/ReminderNew";
import ReminderEdit from "./pages/reminders/ReminderEdit";
import ReminderView from "./pages/reminders/ReminderView";
import ProtectedRoute from "./components/ProtectedRoute";

const queryClient = new QueryClient();

const App = () => {
  const [isI18nInitialized, setIsI18nInitialized] = useState(false);

  useEffect(() => {
    initI18n().then(() => setIsI18nInitialized(true));
  }, []);

  if (!isI18nInitialized) {
    return (
      <div className="flex min-h-screen items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
        </div>
      </div>
    );
  }

  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter basename={import.meta.env.BASE_URL}>
        <LanguageProvider>
          <TooltipProvider>
            <Toaster />
            <Sonner />
            <Routes>
              <Route path="/" element={<Navigate to="/login" replace />} />
              <Route path="/login" element={<Login />} />
              <Route path="/signup" element={<Signup />} />
              <Route path="/forgot-password" element={<ForgotPassword />} />
              {/* Rotas protegidas (necessitam autenticação) */}
              <Route element={<ProtectedRoute />}>
                <Route path="/dashboard" element={<Dashboard />} />
                <Route path="/reports" element={<Reports />} />
                <Route path="/financial" element={<Financial />} />
                <Route path="/lembretes" element={<Reminders />} />
                <Route path="/lembretes/novo" element={<ReminderNew />} />
                <Route path="/lembretes/editar/:id" element={<ReminderEdit />} />
                <Route path="/lembretes/ver/:id" element={<ReminderView />} />
                <Route path="/orcamentos" element={<Budgets />} />
                <Route path="/orcamentos/novo" element={<BudgetNew />} />
                <Route path="/orcamentos/ver/:id" element={<BudgetView />} />
                <Route path="/orcamentos/editar/:id" element={<BudgetEdit />} />
              </Route>
              {/* ADD ALL CUSTOM ROUTES ABOVE THE CATCH-ALL "*" ROUTE */}
              <Route path="*" element={<NotFound />} />
            </Routes>
          </TooltipProvider>
        </LanguageProvider>
      </BrowserRouter>
    </QueryClientProvider>
  );
};

export default App;
