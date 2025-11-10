
import React from 'react';
import { useI18n } from '../hooks/useI18n';

const SearchIcon: React.FC<{ className?: string }> = ({ className }) => <svg className={className} xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>;
const BellIcon: React.FC<{ className?: string }> = ({ className }) => <svg className={className} xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>;

const Header: React.FC = () => {
    const {t} = useI18n();
  return (
    <header className="bg-white border-b border-slate-200 p-4 sticky top-0 z-10">
      <div className="flex items-center justify-between">
        <div className="relative w-full max-w-xs">
          <SearchIcon className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" />
          <input
            type="text"
            placeholder={t('dashboard.search_placeholder')}
            className="pl-10 w-full px-3 py-2 text-slate-700 bg-slate-100 border border-transparent rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-orange-500 focus:bg-white"
          />
        </div>
        <div className="flex items-center space-x-4">
            <button className="p-2 rounded-full text-slate-500 hover:bg-slate-100 hover:text-slate-800 relative">
                <BellIcon className="h-6 w-6"/>
                <span className="absolute top-1 right-1 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
            </button>
            <div className="flex items-center space-x-2">
                <img src="https://picsum.photos/100/100?random=10" alt="User Avatar" className="h-10 w-10 rounded-full" />
                <div>
                    <div className="font-semibold text-slate-800 text-sm">Usuário Teste</div>
                    <div className="text-xs text-slate-500">Admin</div>
                </div>
            </div>
        </div>
      </div>
    </header>
  );
};

export default Header;
   