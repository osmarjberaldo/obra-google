import { useLanguage } from '@/contexts/LanguageContext';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Globe } from 'lucide-react';

export const LanguageSelector = () => {
  const { language, setLanguage } = useLanguage();

  const languages = {
    'pt-BR': 'Português',
    'en': 'English',
    'es': 'Español',
  };

  // Obter apenas as duas primeiras letras do código do idioma para exibição compacta
  const getLanguageCode = (lang: string) => {
    return lang.split('-')[0].toUpperCase();
  };

  return (
    <Select value={language} onValueChange={(value) => setLanguage(value as any)}>
      {/* Ícone-only no mobile, trigger completo no desktop */}
      <SelectTrigger className="w-9 p-2 md:w-[150px] md:px-3">
        <div className="flex items-center md:gap-2 justify-center md:justify-start">
          <Globe className="h-4 w-4" />
          <span className="hidden md:inline">
            {languages[language as keyof typeof languages]}
          </span>
          <span className="inline md:hidden text-xs font-medium">
            {getLanguageCode(language)}
          </span>
        </div>
      </SelectTrigger>
      <SelectContent>
        {Object.entries(languages).map(([code, name]) => (
          <SelectItem key={code} value={code}>
            <div className="flex items-center gap-2">
              <span>{name}</span>
              <span className="text-xs text-muted-foreground">
                ({getLanguageCode(code)})
              </span>
            </div>
          </SelectItem>
        ))}
      </SelectContent>
    </Select>
  );
};