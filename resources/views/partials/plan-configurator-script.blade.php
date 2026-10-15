<script>
    window.planConfigurator = window.planConfigurator || function planConfigurator(catalog, labels, options = {}) {
        const initial = options.initial || {};
        return {
            catalog,
            labels,
            wirePath: options.wirePath || 'data.plan_config',
            period: initial.period || 'Year',
            modules: Array.isArray(initial.modules) ? [...initial.modules] : [],
            activeRecommendation: null,
            employees: initial.employees ?? catalog?.quotas?.find(q => q.key === 'employees')?.included ?? 5,
            establishments: initial.establishments ?? catalog?.quotas?.find(q => q.key === 'establishments')?.included ?? 1,
            screen_devices: initial.screen_devices ?? catalog?.quotas?.find(q => q.key === 'screen_devices')?.included ?? 1,
            pos_devices: initial.pos_devices ?? catalog?.quotas?.find(q => q.key === 'pos_devices')?.included ?? 1,
            waiter_seats: initial.waiter_seats ?? catalog?.quotas?.find(q => q.key === 'waiter_seats')?.included ?? 1,
            kitchen_devices: initial.kitchen_devices ?? catalog?.quotas?.find(q => q.key === 'kitchen_devices')?.included ?? 1,
            quotaCapKey: null,
            coupon_input: initial.coupon_code || '',
            coupon_code: '',
            coupon_discount: 0,
            coupon_label: '',
            coupon_message: '',
            coupon_ok: false,
            coupon_loading: false,
            init() {
                this.employees = this.clampEmployees(this.employees);
                this.pos_devices = this.clampPlainQuota('pos_devices', this.pos_devices);
                this.waiter_seats = this.clampPlainQuota('waiter_seats', this.waiter_seats);
                this.kitchen_devices = this.clampPlainQuota('kitchen_devices', this.kitchen_devices);
                this.syncToWire();
                this.$watch('modules', () => { this.refreshCouponDiscount(); this.syncToWire(); });
                this.$watch('employees', () => { this.refreshCouponDiscount(); this.syncToWire(); });
                this.$watch('establishments', () => { this.refreshCouponDiscount(); this.syncToWire(); });
                this.$watch('screen_devices', () => { this.refreshCouponDiscount(); this.syncToWire(); });
                this.$watch('pos_devices', () => { this.refreshCouponDiscount(); this.syncToWire(); });
                this.$watch('waiter_seats', () => { this.refreshCouponDiscount(); this.syncToWire(); });
                this.$watch('kitchen_devices', () => { this.refreshCouponDiscount(); this.syncToWire(); });
                this.$watch('period', () => { this.refreshCouponDiscount(); this.syncToWire(); });
                if (this.coupon_input) {
                    this.applyCoupon();
                }
            },
            applyRecommendation(rec) {
                const extras = Math.max(0, this.employees - this.includedEmployees());
                const available = new Set((this.catalog.modules || []).map(m => m.key));
                const selected = [];
                (rec.modules || []).forEach(key => {
                    if (!available.has(key) || key === 'platform') return;
                    const mod = (this.catalog.modules || []).find(m => m.key === key);
                    selected.push(key);
                    (mod?.requires || []).forEach(r => {
                        if (available.has(r) && r !== 'platform' && !selected.includes(r)) selected.push(r);
                    });
                });
                this.modules = Array.from(new Set(selected));
                this.activeRecommendation = rec.key;

                const quotas = rec.quotas || {};
                Object.keys(quotas).forEach(key => {
                    if (key === 'employees') return;
                    const def = (this.catalog.quotas || []).find(q => q.key === key);
                    if (!def) return;
                    const value = Math.min(def.max, Math.max(def.min, Number(quotas[key])));
                    this.setQuotaValue(key, value);
                });
                if (quotas.employees != null) {
                    this.employees = this.clampEmployees(Number(quotas.employees));
                } else {
                    this.employees = this.clampEmployees(this.includedEmployees() + extras);
                }
            },
            clearRecommendation() {
                this.activeRecommendation = null;
                this.modules = [];
                (this.catalog.quotas || []).forEach(q => {
                    if (q.key === 'employees') return;
                    this.setQuotaValue(q.key, q.included);
                });
                this.employees = this.clampEmployees(this.includedEmployees());
            },
            modulesFor(groupKey) {
                return (this.catalog.modules || []).filter(m => m.group === groupKey && !m.locked && m.key !== 'platform');
            },
            isCore(modOrKey) {
                return false;
            },
            get visibleQuotas() {
                const inlineKeys = new Set(
                    (this.catalog.modules || [])
                        .map(m => this.resolveInlineQuotaKey(m))
                        .filter(Boolean)
                );
                // Always keep these inside their module cards — never the bottom strip.
                ['pos_devices', 'waiter_seats', 'kitchen_devices', 'employees', 'screen_devices'].forEach(k => inlineKeys.add(k));

                return (this.catalog.quotas || []).filter(quota => {
                    if (inlineKeys.has(quota.key)) return false;
                    if (!quota.linked_module) return true;
                    return this.modules.includes(quota.linked_module);
                });
            },
            resolveInlineQuotaKey(mod) {
                if (mod?.inline_quota) return mod.inline_quota;
                return ({
                    cashier_pos: 'pos_devices',
                    waiter_app: 'waiter_seats',
                    kitchen_app: 'kitchen_devices',
                    employee_schedule: 'employees',
                    digital_screens: 'screen_devices',
                })[mod?.key] || null;
            },
            inlineQuota(mod) {
                const key = this.resolveInlineQuotaKey(mod);
                if (!key) return null;
                return (this.catalog.quotas || []).find(q => q.key === key) || null;
            },
            inlineQuotaLabel(mod) {
                const key = this.resolveInlineQuotaKey(mod);
                const map = {
                    employees: this.labels.employees_count,
                    pos_devices: this.labels.pos_devices_count,
                    waiter_seats: this.labels.waiter_seats_count,
                    kitchen_devices: this.labels.kitchen_devices_count,
                };
                return map[key] || this.inlineQuota(mod)?.name || '';
            },
            inlineQuotaHint(mod) {
                const key = this.resolveInlineQuotaKey(mod);
                const map = {
                    employees: this.labels.employees_quota_hint,
                    pos_devices: this.labels.pos_devices_hint,
                    waiter_seats: this.labels.waiter_seats_hint,
                    kitchen_devices: this.labels.kitchen_devices_hint,
                };
                return map[key] || '';
            },
            ensureModuleForQuota(mod) {
                const key = mod?.key;
                if (!key || this.isSelected(key)) return;
                this.toggle(key);
            },
            bumpInline(mod, delta) {
                const key = this.resolveInlineQuotaKey(mod);
                if (!key) return;
                if (!this.isSelected(mod.key)) {
                    this.ensureModuleForQuota(mod);
                    // After selecting, apply the bump on next tick so state is ready.
                    this.$nextTick(() => this.bump(key, delta));
                    return;
                }
                this.bump(key, delta);
            },
            quotaValue(key) {
                if (key === 'employees') return this.employees;
                if (key === 'establishments') return this.establishments;
                if (key === 'screen_devices') return this.screen_devices;
                if (key === 'pos_devices') return this.pos_devices;
                if (key === 'waiter_seats') return this.waiter_seats;
                if (key === 'kitchen_devices') return this.kitchen_devices;
                return 0;
            },
            quotaIncluded(quota) {
                if (quota?.key === 'employees') return this.includedEmployees();
                return Number(quota?.included ?? 0);
            },
            isQuotaAtMax(key) {
                const quota = (this.catalog.quotas || []).find(q => q.key === key);
                if (!quota) return false;
                return this.quotaValue(key) >= Number(quota.max ?? 9999);
            },
            clampPlainQuota(key, value) {
                const quota = (this.catalog.quotas || []).find(q => q.key === key) || {};
                const min = Number(quota.min ?? 0);
                const max = Number(quota.max ?? 9999);
                const fallback = Number(quota.included ?? min);
                const n = Number(value || 0);
                const base = n > 0 ? n : fallback;
                return Math.min(max, Math.max(min, base));
            },
            resolvedModules() {
                const byKey = new Map((this.catalog.modules || []).map(m => [m.key, m]));
                const resolved = [];
                const queue = [...(this.modules || [])];
                while (queue.length) {
                    const key = queue.shift();
                    if (!byKey.has(key) || resolved.includes(key)) continue;
                    resolved.push(key);
                    (byKey.get(key).requires || []).forEach(r => queue.push(r));
                }
                return resolved;
            },
            includedEmployees() {
                const quota = (this.catalog.quotas || []).find(q => q.key === 'employees') || {};
                const base = Number(quota.included ?? 0);
                const min = Number(quota.min ?? 0);
                let seats = 0;
                const selected = new Set(this.resolvedModules());
                (this.catalog.modules || []).forEach(mod => {
                    if (!selected.has(mod.key)) return;
                    seats += Number(mod.included_employees == null ? 1 : mod.included_employees);
                });
                return Math.max(min, base + seats);
            },
            clampEmployees(value) {
                const quota = (this.catalog.quotas || []).find(q => q.key === 'employees') || {};
                const max = Number(quota.max ?? 9999);
                const floor = Math.max(Number(quota.min ?? 0), this.includedEmployees());
                const n = Number(value || 0);
                return Math.min(max, Math.max(floor, n));
            },
            employeeSeatLabel(count) {
                return String(this.labels.module_employees || ':count').replace(':count', String(Number(count || 0)));
            },
            setQuotaValue(key, value) {
                if (key === 'employees') this.employees = value;
                else if (key === 'establishments') this.establishments = value;
                else if (key === 'screen_devices') this.screen_devices = value;
                else if (key === 'pos_devices') this.pos_devices = value;
                else if (key === 'waiter_seats') this.waiter_seats = value;
                else if (key === 'kitchen_devices') this.kitchen_devices = value;
            },
            isSelected(key) {
                return this.modules.includes(key);
            },
            requiredNames(mod) {
                return (mod.requires || [])
                    .map(key => (this.catalog.modules || []).find(m => m.key === key)?.name || key)
                    .join(', ');
            },
            requiresAnyNames(mod) {
                return (mod.requires_any || [])
                    .map(key => (this.catalog.modules || []).find(m => m.key === key)?.name || key)
                    .join(', ');
            },
            get softDependencyBroken() {
                return (this.catalog.modules || []).some(mod => {
                    if (!this.modules.includes(mod.key)) return false;
                    const any = mod.requires_any || [];
                    if (!any.length) return false;
                    return !any.some(k => this.modules.includes(k));
                });
            },
            toggle(key) {
                this.activeRecommendation = null;
                this.quotaCapKey = null;
                const extras = Math.max(0, this.employees - this.includedEmployees());
                if (this.isSelected(key)) {
                    this.modules = this.modules.filter(k => k !== key);
                    this.modules = this.modules.filter(k => {
                        const mod = (this.catalog.modules || []).find(m => m.key === k);
                        return !(mod?.requires || []).includes(key);
                    });
                    if (key === 'digital_screens') {
                        const q = (this.catalog.quotas || []).find(item => item.key === 'screen_devices');
                        this.screen_devices = q?.included ?? 1;
                    }
                    if (key === 'cashier_pos') {
                        this.pos_devices = this.clampPlainQuota('pos_devices', (this.catalog.quotas || []).find(q => q.key === 'pos_devices')?.included ?? 1);
                        // Drop waiter/kitchen quotas if their modules were removed with cashier.
                        if (!this.modules.includes('waiter_app')) {
                            this.waiter_seats = this.clampPlainQuota('waiter_seats', (this.catalog.quotas || []).find(q => q.key === 'waiter_seats')?.included ?? 1);
                        }
                        if (!this.modules.includes('kitchen_app')) {
                            this.kitchen_devices = this.clampPlainQuota('kitchen_devices', (this.catalog.quotas || []).find(q => q.key === 'kitchen_devices')?.included ?? 1);
                        }
                    }
                    if (key === 'waiter_app') {
                        this.waiter_seats = this.clampPlainQuota('waiter_seats', (this.catalog.quotas || []).find(q => q.key === 'waiter_seats')?.included ?? 1);
                    }
                    if (key === 'kitchen_app') {
                        this.kitchen_devices = this.clampPlainQuota('kitchen_devices', (this.catalog.quotas || []).find(q => q.key === 'kitchen_devices')?.included ?? 1);
                    }
                    if (key === 'employee_schedule') {
                        this.employees = this.clampEmployees(this.includedEmployees());
                    }
                } else {
                    const mod = (this.catalog.modules || []).find(m => m.key === key);
                    const next = new Set(this.modules);
                    next.add(key);
                    (mod?.requires || []).forEach(r => next.add(r));
                    this.modules = Array.from(next);
                    if (key === 'cashier_pos') {
                        this.pos_devices = this.clampPlainQuota('pos_devices', this.pos_devices);
                    }
                    if (key === 'waiter_app') {
                        this.waiter_seats = this.clampPlainQuota('waiter_seats', this.waiter_seats);
                    }
                    if (key === 'kitchen_app') {
                        this.kitchen_devices = this.clampPlainQuota('kitchen_devices', this.kitchen_devices);
                    }
                }
                this.employees = this.clampEmployees(this.includedEmployees() + extras);
            },
            bump(key, delta) {
                this.activeRecommendation = null;
                const quota = (this.catalog.quotas || []).find(q => q.key === key);
                if (!quota) return;
                const current = this.quotaValue(key);
                const min = key === 'employees'
                    ? Math.max(Number(quota.min ?? 0), this.includedEmployees())
                    : Number(quota.min ?? 0);
                const max = Number(quota.max ?? 9999);
                if (delta > 0 && current >= max) {
                    this.quotaCapKey = key;
                    return;
                }
                const next = Math.min(max, Math.max(min, current + delta));
                this.quotaCapKey = next >= max ? key : null;
                this.setQuotaValue(key, next);
            },
            setPeriod(period) {
                this.period = period;
            },
            yearlyMonthsCharged() {
                return Number(this.catalog.yearly_months_charged || 10);
            },
            yearlyGifted() {
                const gifted = this.catalog.yearly_months_gifted;
                if (gifted == null) {
                    return Math.max(0, 12 - this.yearlyMonthsCharged());
                }

                return Math.max(0, Number(gifted));
            },
            yearlyListValue() {
                return this.monthlyTotal * 12;
            },
            yearlyGiftAmount() {
                return this.monthlyTotal * this.yearlyGifted();
            },
            isYearlyDeal() {
                return this.period === 'Year' && this.yearlyGifted() > 0;
            },
            effectiveMonthly(amount) {
                const monthly = Number(amount || 0);
                if (! this.isYearlyDeal()) {
                    return monthly;
                }

                return monthly * this.yearlyMonthsCharged() / 12;
            },
            format(value) {
                return Math.round(Number(value || 0)).toLocaleString();
            },
            formatDeal(value) {
                return Number(value || 0).toLocaleString(undefined, {
                    minimumFractionDigits: 1,
                    maximumFractionDigits: 1,
                });
            },
            displayMonthly(amount) {
                return this.isYearlyDeal()
                    ? this.formatDeal(this.effectiveMonthly(amount))
                    : this.format(amount);
            },
            get baseLineItems() {
                const items = [];
                // Foundation (products / employees / branches) is free — never billed.
                (this.catalog.modules || []).forEach(mod => {
                    if (mod.key === 'platform' || mod.locked) return;
                    if (this.modules.includes(mod.key)) {
                        items.push({ key: mod.key, label: mod.name, total: mod.price_month, type: 'module' });
                    }
                });

                const pushQuotaExtra = (quota) => {
                    if (!quota) return;
                    const count = this.quotaValue(quota.key);
                    const extra = Math.max(0, count - this.quotaIncluded(quota));
                    if (extra <= 0) return;
                    items.push({
                        key: quota.key + '_extra',
                        label: quota.name + ' (+' + extra + ')',
                        total: extra * quota.price_per_extra_month,
                        type: 'quota',
                    });
                };

                (this.catalog.modules || []).forEach(mod => {
                    if (!this.resolveInlineQuotaKey(mod) || !this.modules.includes(mod.key)) return;
                    pushQuotaExtra(this.inlineQuota(mod));
                });

                this.visibleQuotas.forEach(pushQuotaExtra);
                return items;
            },
            get lineItems() {
                const months = this.period === 'Year' ? this.yearlyMonthsCharged() : 1;
                return this.baseLineItems.map(line => {
                    const monthly = Number(line.total || 0);
                    return {
                        ...line,
                        total: monthly * months,
                        meta: this.period === 'Year'
                            ? `${this.format(monthly)} × ${months}`
                            : `/ ${this.labels.per_month}`,
                    };
                });
            },
            get monthlyTotal() {
                return this.baseLineItems.reduce((sum, line) => sum + Number(line.total || 0), 0);
            },
            get periodSubtotal() {
                if (this.period === 'Year') {
                    return this.monthlyTotal * this.yearlyMonthsCharged();
                }
                return this.monthlyTotal;
            },
            get periodTotal() {
                return Math.max(0, this.periodSubtotal - Number(this.coupon_discount || 0));
            },
            async applyCoupon() {
                if (this.coupon_loading) return;
                this.coupon_loading = true;
                this.coupon_message = '';
                try {
                    const result = await this.$wire.previewPlanCoupon(this.coupon_input, this.periodSubtotal);
                    if (result?.ok) {
                        this.coupon_code = result.code || '';
                        this.coupon_input = this.coupon_code;
                        this.coupon_discount = Number(result.discount || 0);
                        this.coupon_label = result.label || this.coupon_code;
                        this.coupon_message = result.message || '';
                        this.coupon_ok = true;
                    } else {
                        this.coupon_code = '';
                        this.coupon_discount = 0;
                        this.coupon_label = '';
                        this.coupon_message = result?.message || '';
                        this.coupon_ok = false;
                    }
                    this.syncToWire();
                } catch (e) {
                    this.coupon_code = '';
                    this.coupon_discount = 0;
                    this.coupon_label = '';
                    this.coupon_message = '';
                    this.coupon_ok = false;
                    this.syncToWire();
                } finally {
                    this.coupon_loading = false;
                }
            },
            async refreshCouponDiscount() {
                if (!this.coupon_code) {
                    this.coupon_discount = 0;
                    return;
                }
                try {
                    const result = await this.$wire.previewPlanCoupon(this.coupon_code, this.periodSubtotal);
                    if (result?.ok) {
                        this.coupon_discount = Number(result.discount || 0);
                        this.coupon_label = result.label || this.coupon_code;
                        this.coupon_ok = true;
                    } else {
                        this.clearCoupon(false);
                        this.coupon_message = result?.message || '';
                        this.coupon_ok = false;
                    }
                    this.syncToWire();
                } catch (e) {}
            },
            clearCoupon(clearInput = true) {
                this.coupon_code = '';
                this.coupon_discount = 0;
                this.coupon_label = '';
                this.coupon_message = '';
                this.coupon_ok = false;
                if (clearInput) this.coupon_input = '';
                this.syncToWire();
            },
            syncToWire() {
                try {
                    this.$wire.set(this.wirePath, {
                        period: this.period,
                        modules: this.modules,
                        employees: this.employees,
                        establishments: this.establishments,
                        screen_devices: this.modules.includes('digital_screens') ? this.screen_devices : 0,
                        pos_devices: this.modules.includes('cashier_pos') ? this.pos_devices : 0,
                        waiter_seats: this.modules.includes('waiter_app') ? this.waiter_seats : 0,
                        kitchen_devices: this.modules.includes('kitchen_app') ? this.kitchen_devices : 0,
                        coupon_code: this.coupon_code || null,
                    });
                } catch (e) {}
            },
        };
    };
</script>