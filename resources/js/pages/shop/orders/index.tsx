import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Pagination, PaginationContent, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { getOrderPaymentStatusText } from '@/helpers/orders-helper';
import AppLayout from '@/layouts/app-layout';
import { Order } from '@/types/model-types';
import { Head, Link, router } from '@inertiajs/react';
import { ChevronDown, ChevronDownSquare, EyeIcon } from 'lucide-react';
import React, { useState } from 'react';

interface Props {
    orders: {
        data: Order[];
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
}

export default function Index(props: Props) {
    const { orders } = props;

    const [search, setSearch] = useState('');
    const [sort, setSort] = useState<string | null>(null);
    const [filters, setFilters] = useState<{
        status?: string;
        purchase_pool_id?: string;
    }>({});
    const [columns, setColumns] = useState(['id', 'name', 'email', 'phone', 'status', 'purchase_pool', 'actions']);

    const handleSearchChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        setSearch(event.target.value);
        router.get(route('orders.index'), { search: event.target.value, ...filters, sort }, { preserveState: true });
    };

    const handleSort = (column: string) => {
        const newSort = sort === column ? `-${column}` : column;
        setSort(newSort);
        router.get(route('orders.index'), { search, ...filters, sort: newSort }, { preserveState: true });
    };

    const handleFilterChange = (filterName: keyof typeof filters, value: string) => {
        const newFilters = { ...filters, [filterName]: value === '' ? undefined : value };
        setFilters(newFilters);
        router.get(route('orders.index'), { search, filter: newFilters, sort }, { preserveState: true });
    };

    const toggleColumn = (column: string) => {
        setColumns((prevColumns) => (prevColumns.includes(column) ? prevColumns.filter((c) => c !== column) : [...prevColumns, column]));
    };

    const paginationLinks = () => {
        const links = [];
        for (let i = 1; i < orders.links.length - 1; i++) {
            const link = orders.links[i];
            if (link.url) {
                links.push(
                    <PaginationItem key={i} active={link.active}>
                        <Link href={link.url}>{link.label}</Link>
                    </PaginationItem>,
                );
            } else if (link.label === '...') {
                links.push(<span key={i}>...</span>);
            }
        }
        return links;
    };

    const links = {
        prev: orders.links[0]?.url || null,
        next: orders.links[orders.links.length - 1]?.url || null,
    };

    console.log(orders.links);

    return (
        <AppLayout>
            <Head title="Orders" />
            <div className="space-y-4">
                <div className="flex items-center justify-between gap-2 p-2">
                    <Input type="text" placeholder="Search orders..." value={search} onChange={handleSearchChange} />
                    <div className="flex items-center space-x-2">
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="outline" size="sm">
                                    <ChevronDown className="mr-2 h-4 w-4" /> Filters
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuLabel>Filter By</DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Status</label>
                                    <Input
                                        type="text"
                                        className="w-32"
                                        value={filters.status || ''}
                                        onChange={(e) => handleFilterChange('status', e.target.value)}
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Purchase Pool</label>
                                    <select
                                        className="w-32 rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        value={filters.purchase_pool_id || ''}
                                        onChange={(e) => handleFilterChange('purchase_pool_id', e.target.value)}
                                    >
                                        <option value="">All</option>
                                        {/*{purchase_pool && purchase_pool.name}*/}
                                    </select>
                                </div>
                            </DropdownMenuContent>
                        </DropdownMenu>

                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="outline" size="sm">
                                    <ChevronDownSquare className="mr-2 h-4 w-4" /> Columns
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuLabel>Toggle Columns</DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                <DropdownMenuCheckboxItem checked={columns.includes('id')} onCheckedChange={() => toggleColumn('id')}>
                                    ID
                                </DropdownMenuCheckboxItem>
                                <DropdownMenuCheckboxItem checked={columns.includes('name')} onCheckedChange={() => toggleColumn('name')}>
                                    Name
                                </DropdownMenuCheckboxItem>
                                <DropdownMenuCheckboxItem checked={columns.includes('email')} onCheckedChange={() => toggleColumn('email')}>
                                    Email
                                </DropdownMenuCheckboxItem>
                                <DropdownMenuCheckboxItem checked={columns.includes('phone')} onCheckedChange={() => toggleColumn('phone')}>
                                    Phone
                                </DropdownMenuCheckboxItem>
                                <DropdownMenuCheckboxItem checked={columns.includes('address')} onCheckedChange={() => toggleColumn('address')}>
                                    Address
                                </DropdownMenuCheckboxItem>
                                <DropdownMenuCheckboxItem checked={columns.includes('status')} onCheckedChange={() => toggleColumn('status')}>
                                    Status
                                </DropdownMenuCheckboxItem>
                                <DropdownMenuCheckboxItem
                                    checked={columns.includes('purchase_pool')}
                                    onCheckedChange={() => toggleColumn('purchase_pool')}
                                >
                                    Purchase Pool
                                </DropdownMenuCheckboxItem>
                            </DropdownMenuContent>
                        </DropdownMenu>

                        {/*<Link href={route('orders.create')}>*/}
                        {/*    <Button>Create New</Button>*/}
                        {/*</Link>*/}
                    </div>
                </div>
                <Table>
                    <TableHeader>
                        <TableRow>
                            {columns.includes('name') && (
                                <TableHead className="cursor-pointer" onClick={() => handleSort('name')}>
                                    Name {sort === 'name' ? '↑' : sort === '-name' ? '↓' : ''}
                                </TableHead>
                            )}
                            {columns.includes('email') && (
                                <TableHead className="cursor-pointer" onClick={() => handleSort('email')}>
                                    Customer Email {sort === 'email' ? '↑' : sort === '-email' ? '↓' : ''}
                                </TableHead>
                            )}
                            {columns.includes('phone') && (
                                <TableHead className="cursor-pointer" onClick={() => handleSort('phone')}>
                                    Customer Phone {sort === 'phone' ? '↑' : sort === '-phone' ? '↓' : ''}
                                </TableHead>
                            )}
                            {columns.includes('status') && (
                                <TableHead className="cursor-pointer" onClick={() => handleSort('status')}>
                                    Status {sort === 'status' ? '↑' : sort === '-status' ? '↓' : ''}
                                </TableHead>
                            )}
                            {columns.includes('actions') && <TableHead>Actions</TableHead>}
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {orders.data.map((order: Order) => (
                            <TableRow key={order.id}>
                                {columns.includes('name') && <TableCell>#{order.id}</TableCell>}
                                {columns.includes('email') && <TableCell>{order.email}</TableCell>}
                                {columns.includes('phone') && <TableCell>{order.phone}</TableCell>}
                                {columns.includes('status') && <TableCell>{getOrderPaymentStatusText(order.status)}</TableCell>}
                                {columns.includes('actions') && (
                                    <TableCell>
                                        <div className="flex items-center space-x-2">
                                            <Link href={route('orders.show', order.id)}>
                                                <Button size="sm" variant="outline">
                                                    <EyeIcon className="h-4 w-4" /> View
                                                </Button>
                                            </Link>
                                        </div>
                                    </TableCell>
                                )}
                            </TableRow>
                        ))}
                        {orders.data.length === 0 && (
                            <TableRow>
                                <TableCell colSpan={columns.length} className="py-4 text-center">
                                    No orders found.
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>

                {orders.links && (
                    <Pagination>
                        <PaginationContent className="justify-center gap-2">
                            <PaginationItem>
                                <PaginationPrevious hidden={!orders.links.prev} href={orders.links.prev} />
                            </PaginationItem>
                            {paginationLinks()}
                            <PaginationItem>
                                <PaginationNext hidden={!orders.links.next} href={orders.links.next} />
                            </PaginationItem>
                        </PaginationContent>
                    </Pagination>
                )}
            </div>
        </AppLayout>
    );
}
