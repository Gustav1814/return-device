import { createContext, useContext } from 'react';

export type SaasMeProfile = {
  name?: string;
  email?: string;
  phone?: string | null;
  company_id?: number;
  /** Same rule as classic: logged-in user's company is RR / platform. */
  is_rr_company?: boolean;
};

const SaasMeContext = createContext<SaasMeProfile | null>(null);

export const SaasMeProvider = SaasMeContext.Provider;

export function useSaasMe(): SaasMeProfile | null {
  return useContext(SaasMeContext);
}
