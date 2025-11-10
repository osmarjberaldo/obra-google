
import React, { useState, useCallback } from 'react';
import LoginPage from './pages/LoginPage';
import SignupPage from './pages/SignupPage';
import Dashboard from './pages/Dashboard';

export type Page = 'login' | 'signup' | 'dashboard';

const App: React.FC = () => {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [currentPage, setCurrentPage] = useState<Page>('login');

  const handleLoginSuccess = useCallback(() => {
    setIsAuthenticated(true);
    setCurrentPage('dashboard');
  }, []);

  const handleLogout = useCallback(() => {
    setIsAuthenticated(false);
    setCurrentPage('login');
  }, []);

  const navigateTo = useCallback((page: Page) => {
    setCurrentPage(page);
  }, []);

  const renderPage = () => {
    if (isAuthenticated) {
      return <Dashboard onLogout={handleLogout} />;
    }
    switch (currentPage) {
      case 'signup':
        return <SignupPage onNavigate={navigateTo} />;
      case 'login':
      default:
        return <LoginPage onLoginSuccess={handleLoginSuccess} onNavigate={navigateTo} />;
    }
  };

  return (
    <div className="min-h-screen font-sans">
      {renderPage()}
    </div>
  );
};

export default App;
