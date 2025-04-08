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
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { ChevronDown, ChevronDownSquare } from 'lucide-react';
import React, { useState } from 'react';
import { PurchasePool } from '@/types/model-types'; // Adjust the import path as needed

interface Props {
    purchasePools: {
        data: PurchasePool[];
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
}

export default function PurchasePoolIndex({ purchasePools: initialPurchasePools }: Props) {
    const { props } = usePage();
    const { purchasePools } = props as unknown as { purchasePools: Props['purchasePools'] };

    const [search, setSearch] = useState('');
    const [sort, setSort] = useState<string | null>(null);
    const [columns, setColumns] = useState([
        'id',
        'status',
        'start_date',
        'end_date',
        'target_delivery_date',
        'actions',
    ]);

    const handleSearchChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        setSearch(event.target.value);
        router.get(route('purchase-pools.index'), { search: event.target.value, sort }, { preserveState: true });
    };

    const handleSort = (column: string) => {
        const newSort = sort === column ? `-${column}` : column;
        setSort(newSort);
        router.get(route('purchase-pools.index'), { search, sort: newSort }, { preserveState: true });
    };

    const toggleColumn = (column: string) => {
        setColumns((prevColumns) =>
            prevColumns.includes(column) ? prevColumns.filter((c) => c !== column) : [...prevColumns, column]
        );
    };

    const paginationLinks = () => {
        const links = [];
        for (let i = 1; i < purchasePools.links.length - 1; i++) {
            const link = purchasePools.links[i];
            if (link.url) {
                links.push(
                    <PaginationItem key={i} active={link.active}>
                        <Link href={link.url}>{link.label}</Link>
                    </PaginationItem>
                );
            } else if (link.label === '...') {
                links.push(<span key={i}>...</span>);
            }
        }
        return links;
    };

    const links = {
        prev: purchasePools.links[0]?.url || null,
        next: purchasePools.links[purchasePools.links.length - 1]?.url || null,
    };

    return (
        <AppLayout>
            <Head title="Purchase Pools" />
            <div className="space-y-4">
                <div className="flex items-center justify-between gap-2 p-2">
                    <Input type="text" placeholder="Search purchase pools..." value={search} onChange={handleSearchChange} />
                    <div className="flex items-center space-x-2">
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="outline" size="sm">
                                    <ChevronDown className="mr-2 h-4 w-4" /> Columns
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuLabel>Toggle Columns</DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                <DropdownMenuCheckboxItem checked={columns.includes('id')} onCheckedChange={() => toggleColumn('id')}>
                                    ID
                                </DropdownMenuCheckboxItem>
                                <DropdownMenuCheckboxItem checked={columns.includes('status')} onCheckedChange={() => toggleColumn('name')}>
                                    Status
                                </DropdownMenuCheckboxItem>
                                <DropdownMenuCheckboxItem checked={columns.includes('start_date')} onCheckedChange={() => toggleColumn('start_date')}>
                                    Start date
                                </DropdownMenuCheckboxItem>
                                <DropdownMenuCheckboxItem checked={columns.includes('end_date')} onCheckedChange={() => toggleColumn('end_date')}>
                                    End date
                                </DropdownMenuCheckboxItem>
                                <DropdownMenuCheckboxItem checked={columns.includes('target_delivery_date')} onCheckedChange={() => toggleColumn('target_delivery_date')}>
                                    Target delivery date
                                </DropdownMenuCheckboxItem>
                                <DropdownMenuCheckboxItem checked={columns.includes('actions')} onCheckedChange={() => toggleColumn('actions')}>
                                    Actions
                                </DropdownMenuCheckboxItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            {columns.includes('id') && (
                                <TableHead className="cursor-pointer" onClick={() => handleSort('id')}>
                                    ID {sort === 'id' ? '↑' : sort === '-id' ? '↓' : ''}
                                </TableHead>
                            )}
                            {columns.includes('status') && (
                                <TableHead className="cursor-pointer" onClick={() => handleSort('status')}>
                                    Status {sort === 'status' ? '↑' : sort === '-status' ? '↓' : ''}
                                </TableHead>
                            )}
                            {columns.includes('start_date') && (
                                <TableHead className="cursor-pointer" onClick={() => handleSort('start_date')}>
                                    Start date {sort === 'start_date' ? '↑' : sort === '-start_date' ? '↓' : ''}
                                </TableHead>
                            )}
                            {columns.includes('end_date') && (
                                <TableHead className="cursor-pointer" onClick={() => handleSort('end_date')}>
                                    End date {sort === 'end_date' ? '↑' : sort === '-end_date' ? '↓' : ''}
                                </TableHead>
                            )}
                            {columns.includes('target_delivery_date') && (
                                <TableHead className="cursor-pointer" onClick={() => handleSort('target_delivery_date')}>
                                    Target delivery date {sort === 'target_delivery_date' ? '↑' : sort === '-target_delivery_date' ? '↓' : ''}
                                </TableHead>
                            )}

                            {/*{columns.includes('created_at') && <TableHead>Created At</TableHead>}*/}
                            {/*{columns.includes('updated_at') && <TableHead>Updated At</TableHead>}*/}
                            {columns.includes('actions') && <TableHead className="text-right">Actions</TableHead>}
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {purchasePools.data.map((pool) => (
                            <TableRow key={pool.id}>
                                {columns.includes('id') && <TableCell>{pool.id}</TableCell>}
                                {columns.includes('status') && <TableCell>{pool.status}</TableCell>}
                                {columns.includes('start_date') && <TableCell>{new Date(pool.start_date).toLocaleDateString()}</TableCell>}
                                {columns.includes('end_date') && <TableCell>{new Date(pool.end_date).toLocaleDateString()}</TableCell>}
                                {columns.includes('target_delivery_date') && <TableCell>{new Date(pool.target_delivery_date).toLocaleDateString()}</TableCell>}
                                {/*{columns.includes('created_at') && <TableCell>{new Date(pool.created_at).toLocaleDateString()}</TableCell>}*/}
                                {/*{columns.includes('updated_at') && <TableCell>{new Date(pool.updated_at).toLocaleDateString()}</TableCell>}*/}
                                {columns.includes('actions') && (
                                    <TableCell className="text-right">
                                        <Link href={route('purchase-pools.show', pool.id)}>
                                            <Button size="sm" variant="outline">
                                                View
                                            </Button>
                                        </Link>
                                    </TableCell>
                                )}
                            </TableRow>
                        ))}
                        {purchasePools.data.length === 0 && (
                            <TableRow>
                                <TableCell colSpan={columns.length} className="py-4 text-center">
                                    You are currently not part of any purchase pools. Please <Link href={route('market')} className={'link underline text-accent-foreground'}>make an order</Link> to get started.
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>

                {purchasePools.links && purchasePools.links.length > 3 && (
                    <Pagination>
                        <PaginationContent className="justify-center gap-2">
                            <PaginationItem>
                                <PaginationPrevious hidden={!links.prev} href={links.prev} />
                            </PaginationItem>
                            {paginationLinks()}
                            <PaginationItem>
                                <PaginationNext hidden={!links.next} href={links.next} />
                            </PaginationItem>
                        </PaginationContent>
                    </Pagination>
                )}
            </div>
        </AppLayout>
    );
}
