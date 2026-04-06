/**
 * @license
 * SPDX-License-Identifier: Apache-2.0
 */

import React, { useState } from 'react';
import { motion, AnimatePresence } from 'motion/react';
import { 
  RefreshCw, 
  Mail, 
  Lock, 
  ArrowRight, 
  Github, 
  Chrome, 
  CheckCircle2,
  ShieldCheck,
  Truck,
  CreditCard
} from 'lucide-react';

export default function App() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [isSuccess, setIsSuccess] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    
    // Simulate login
    setTimeout(() => {
      setIsLoading(false);
      setIsSuccess(true);
      setTimeout(() => {
        window.location.href = '/frontend/index.html';
      }, 1500);
    }, 2000);
  };

  return (
    <div className="min-h-screen bg-[#000000] flex items-center justify-center p-6 font-sans selection:bg-indigo-500/30 selection:text-white overflow-hidden">
      {/* Background Decorative Blobs */}
      <div className="fixed inset-0 overflow-hidden pointer-events-none">
        <motion.div 
          animate={{ 
            scale: [1, 1.2, 1],
            rotate: [0, 90, 0],
            x: [0, 100, 0],
            y: [0, 50, 0]
          }}
          transition={{ duration: 20, repeat: Infinity, ease: "linear" }}
          className="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] bg-indigo-600/10 rounded-full blur-[120px]" 
        />
        <motion.div 
          animate={{ 
            scale: [1.2, 1, 1.2],
            rotate: [90, 0, 90],
            x: [0, -100, 0],
            y: [0, -50, 0]
          }}
          transition={{ duration: 25, repeat: Infinity, ease: "linear" }}
          className="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] bg-purple-600/10 rounded-full blur-[120px]" 
        />
      </div>

      <motion.div 
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.8, ease: [0.22, 1, 0.36, 1] }}
        className="w-full max-w-[1100px] grid lg:grid-cols-2 bg-black/40 backdrop-blur-2xl rounded-[40px] shadow-[0_32px_64px_-16px_rgba(0,0,0,0.5)] overflow-hidden relative z-10 border border-white/5"
      >
        {/* Left Side: Branding & Features */}
        <div className="hidden lg:flex flex-col justify-between p-16 bg-gradient-to-br from-indigo-950/50 to-black text-white relative overflow-hidden border-r border-white/5">
          <div className="relative z-10">
            <div className="flex items-center gap-3 mb-16">
              <div className="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
                <RefreshCw className="w-7 h-7 text-white" />
              </div>
              <span className="text-2xl font-bold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-white to-white/60">DeviceReturn</span>
            </div>

            <div className="space-y-10">
              <motion.h2 
                initial={{ opacity: 0, x: -20 }}
                animate={{ opacity: 1, x: 0 }}
                transition={{ delay: 0.2 }}
                className="text-5xl font-extrabold leading-tight tracking-tight"
              >
                The modern way to manage <span className="text-indigo-400">IT asset logistics.</span>
              </motion.h2>

              <div className="space-y-8">
                {[
                  { icon: Truck, title: "Automated Shipping", desc: "Instant label generation and pickup scheduling." },
                  { icon: ShieldCheck, title: "Secure Logistics", desc: "End-to-end tracking and insurance for every asset." },
                  { icon: CreditCard, title: "Easy Payments", desc: "Integrated processing and transparent pricing." }
                ].map((feature, i) => (
                  <motion.div 
                    key={i}
                    initial={{ opacity: 0, x: -20 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: 0.3 + (i * 0.1) }}
                    className="flex gap-5 group"
                  >
                    <div className="w-14 h-14 rounded-2xl bg-white/5 flex items-center justify-center flex-shrink-0 border border-white/10 group-hover:border-indigo-500/50 transition-colors">
                      <feature.icon className="w-7 h-7 text-indigo-400" />
                    </div>
                    <div>
                      <h4 className="font-bold text-xl mb-1">{feature.title}</h4>
                      <p className="text-slate-400 text-base leading-relaxed">{feature.desc}</p>
                    </div>
                  </motion.div>
                ))}
              </div>
            </div>
          </div>

          <div className="relative z-10 pt-12 border-t border-white/10">
            <p className="text-slate-500 text-sm font-medium">
              Trusted by 500+ remote-first companies worldwide.
            </p>
          </div>
        </div>

        {/* Right Side: Login Form */}
        <div className="p-10 lg:p-20 flex flex-col justify-center relative bg-black/20">
          <AnimatePresence mode="wait">
            {!isSuccess ? (
              <motion.div 
                key="login-form"
                initial={{ opacity: 0, x: 20 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: -20 }}
                className="w-full max-w-sm mx-auto"
              >
                <div className="mb-12">
                  <h1 className="text-4xl font-bold text-white mb-3 tracking-tight">Welcome back</h1>
                  <p className="text-slate-400 text-lg">Enter your details to access your dashboard</p>
                </div>

                <form onSubmit={handleSubmit} className="space-y-8">
                  <div className="p-5 bg-indigo-500/5 rounded-3xl border border-indigo-500/10 mb-8 backdrop-blur-sm">
                    <p className="text-xs font-bold text-indigo-400 uppercase tracking-widest mb-2">Demo Credentials</p>
                    <p className="text-sm text-slate-300">Email: <span className="font-mono font-bold text-white">admin@acme.com</span></p>
                    <p className="text-sm text-slate-300">Password: <span className="font-mono font-bold text-white">any password</span></p>
                  </div>

                  <div className="space-y-3">
                    <label className="text-sm font-bold text-slate-300 ml-1">Email Address</label>
                    <div className="relative group">
                      <Mail className="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500 group-focus-within:text-indigo-400 transition-colors" />
                      <input 
                        type="email" 
                        required
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        placeholder="admin@acme.com"
                        className="w-full pl-14 pr-6 py-4 bg-white/5 border border-white/10 rounded-2xl outline-none focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-slate-600 text-white"
                      />
                    </div>
                  </div>

                  <div className="space-y-3">
                    <div className="flex justify-between items-center ml-1">
                      <label className="text-sm font-bold text-slate-300">Password</label>
                      <a href="#" className="text-sm font-bold text-indigo-400 hover:text-indigo-300 transition-colors">Forgot?</a>
                    </div>
                    <div className="relative group">
                      <Lock className="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500 group-focus-within:text-indigo-400 transition-colors" />
                      <input 
                        type="password" 
                        required
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        placeholder="••••••••"
                        className="w-full pl-14 pr-6 py-4 bg-white/5 border border-white/10 rounded-2xl outline-none focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-slate-600 text-white"
                      />
                    </div>
                  </div>

                  <button 
                    type="submit" 
                    disabled={isLoading}
                    className="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-5 rounded-2xl font-bold text-xl shadow-2xl shadow-indigo-500/20 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-3 disabled:opacity-70 disabled:cursor-not-allowed"
                  >
                    {isLoading ? (
                      <div className="w-7 h-7 border-3 border-white/30 border-t-white rounded-full animate-spin" />
                    ) : (
                      <>
                        Sign In <ArrowRight className="w-6 h-6" />
                      </>
                    )}
                  </button>
                </form>

                <div className="mt-12">
                  <div className="relative mb-10">
                    <div className="absolute inset-0 flex items-center"><div className="w-full border-t border-white/5"></div></div>
                    <div className="relative flex justify-center text-sm"><span className="px-6 bg-transparent text-slate-500 font-bold uppercase tracking-widest">Or continue with</span></div>
                  </div>

                  <div className="grid grid-cols-2 gap-5">
                    <button className="flex items-center justify-center gap-3 py-4 border border-white/10 rounded-2xl hover:bg-white/5 transition-all font-bold text-slate-300 hover:text-white">
                      <Chrome className="w-5 h-5" /> Google
                    </button>
                    <button className="flex items-center justify-center gap-3 py-4 border border-white/10 rounded-2xl hover:bg-white/5 transition-all font-bold text-slate-300 hover:text-white">
                      <Github className="w-5 h-5" /> GitHub
                    </button>
                  </div>
                </div>
              </motion.div>
            ) : (
              <motion.div 
                key="success"
                initial={{ opacity: 0, scale: 0.9 }}
                animate={{ opacity: 1, scale: 1 }}
                className="text-center"
              >
                <div className="w-24 h-24 bg-indigo-500/20 text-indigo-400 rounded-full flex items-center justify-center mx-auto mb-8 shadow-2xl shadow-indigo-500/20">
                  <CheckCircle2 className="w-12 h-12" />
                </div>
                <h2 className="text-4xl font-bold text-white mb-3 tracking-tight">Success!</h2>
                <p className="text-slate-400 text-lg">Redirecting to your dashboard...</p>
              </motion.div>
            )}
          </AnimatePresence>

          <div className="mt-auto pt-12 text-center">
            <p className="text-slate-500 text-sm font-medium">
              Don't have an account? <a href="#" className="text-indigo-400 font-bold hover:text-indigo-300 transition-colors">Contact sales</a>
            </p>
          </div>
        </div>
      </motion.div>
    </div>
  );
}
