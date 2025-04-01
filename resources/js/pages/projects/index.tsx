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
import { Pagination, PaginationContent, PaginationItem, PaginationNext } from '@/components/ui/pagination';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { Project } from '@/types/model-types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { ChevronDown, ChevronDownSquare, PencilIcon, Trash2Icon } from 'lucide-react';
import { useState } from 'react';

export default function Index() {
    const { projects } = usePage().props;
    const [search, setSearch] = useState('');
    const [sort, setSort] = useState(null);
    const [filters, setFilters] = useState({});
    const [columns, setColumns] = useState(['id', 'name', 'budget', 'start_date', 'target_completion_date', 'actions']);

    const handleSearchChange = (event) => {
        setSearch(event.target.value);
        router.get(route('projects.index'), { search: event.target.value, ...filters, sort }, { preserveState: true });
    };

    const handleSort = (column) => {
        const newSort = sort === column ? `-${column}` : column;
        setSort(newSort);
        router.get(route('projects.index'), { search, ...filters, sort: newSort }, { preserveState: true });
    };

    const handleFilterChange = (filterName: string, value: unknown) => {
        const newFilters = { ...filters, [filterName]: value };
        setFilters(newFilters);
        router.get(route('projects.index'), { search, filter: newFilters, sort }, { preserveState: true });
    };

    const handleDelete = (id: number) => {
        if (confirm('Are you sure you want to delete this project?')) {
            router.delete(route('projects.destroy', id));
        }
    };

    const toggleColumn = (column) => {
        setColumns((prevColumns) => (prevColumns.includes(column) ? prevColumns.filter((c) => c !== column) : [...prevColumns, column]));
    };

    const paginationLinks = () => {
        const links = [];
        // Laravel pagination includes 'previous' and 'next' as the first and last items
        for (let i = 1; i < projects.links.length - 1; i++) {
            const link = projects.links[i];
            if (link.url) {
                links.push(
                    <PaginationItem key={i} active={link.active}>
                        <Link href={link.url}>{link.label}</Link>
                    </PaginationItem>,
                );
            } else if (link.label === '...') {
                // You might need to add a PaginationEllipsis component from Shadcn UI if you want to represent the ellipsis
                links.push(<span key={i}>...</span>);
            }
        }
        return links;
    };

    const links = {
        prev: projects.links[0]?.url || null,
        next: projects.links[projects.links.length - 1]?.url || null,
    };

    return (
        <AppLayout>
            <Head title="Projects" />
            <div className="space-y-4">
                <div className="flex items-center justify-between gap-2 p-2">
                    <Input type="text" placeholder="Search projects..." value={search} onChange={handleSearchChange} />
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
                                {/* Add your filter options here */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Budget</label>
                                    <Input
                                        type="number"
                                        className="w-32"
                                        value={filters.budget || ''}
                                        onChange={(e) => handleFilterChange('budget', e.target.value)}
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Start Date</label>
                                    <Input
                                        type="date"
                                        className="w-32"
                                        value={filters.start_date || ''}
                                        onChange={(e) => handleFilterChange('start_date', e.target.value)}
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Target Completion Date</label>
                                    <Input
                                        type="date"
                                        className="w-32"
                                        value={filters.target_completion_date || ''}
                                        onChange={(e) => handleFilterChange('target_completion_date', e.target.value)}
                                    />
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
                                <DropdownMenuCheckboxItem checked={columns.includes('budget')} onCheckedChange={() => toggleColumn('budget')}>
                                    Budget
                                </DropdownMenuCheckboxItem>
                                <DropdownMenuCheckboxItem checked={columns.includes('start_date')} onCheckedChange={() => toggleColumn('start_date')}>
                                    Start Date
                                </DropdownMenuCheckboxItem>
                                <DropdownMenuCheckboxItem
                                    checked={columns.includes('target_completion_date')}
                                    onCheckedChange={() => toggleColumn('target_completion_date')}
                                >
                                    Target Completion Date
                                </DropdownMenuCheckboxItem>
                            </DropdownMenuContent>
                        </DropdownMenu>

                        <Link href={route('projects.create')}>
                            <Button>Create New</Button>
                        </Link>
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
                            {columns.includes('name') && (
                                <TableHead className="cursor-pointer" onClick={() => handleSort('name')}>
                                    Name {sort === 'name' ? '↑' : sort === '-name' ? '↓' : ''}
                                </TableHead>
                            )}
                            {columns.includes('budget') && (
                                <TableHead className="cursor-pointer text-right" onClick={() => handleSort('budget')}>
                                    Budget {sort === 'budget' ? '↑' : sort === '-budget' ? '↓' : ''}
                                </TableHead>
                            )}
                            {columns.includes('start_date') && (
                                <TableHead className="cursor-pointer" onClick={() => handleSort('start_date')}>
                                    Start Date {sort === 'start_date' ? '↑' : sort === '-start_date' ? '↓' : ''}
                                </TableHead>
                            )}
                            {columns.includes('target_completion_date') && (
                                <TableHead className="cursor-pointer" onClick={() => handleSort('target_completion_date')}>
                                    Target Completion Date {sort === 'target_completion_date' ? '↑' : sort === '-target_completion_date' ? '↓' : ''}
                                </TableHead>
                            )}
                            {columns.includes('actions') && <TableHead>Actions</TableHead>}
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {projects.data.map((project: Project) => (
                            <TableRow key={project.id}>
                                {columns.includes('id') && <TableCell>{project.id}</TableCell>}
                                {columns.includes('name') && <TableCell>{project.name}</TableCell>}
                                {columns.includes('budget') && <TableCell className="text-right">${project.budget}</TableCell>}
                                {columns.includes('start_date') && <TableCell>{project.start_date}</TableCell>}
                                {columns.includes('target_completion_date') && <TableCell>{project.target_completion_date}</TableCell>}
                                {columns.includes('actions') && (
                                    <TableCell>
                                        <div className="flex items-center space-x-2">
                                            <Link href={route('projects.edit', project.id)}>
                                                <Button size="sm" variant="outline">
                                                    <PencilIcon className="h-4 w-4" /> Edit
                                                </Button>
                                            </Link>
                                            <Button size="sm" variant="destructive" onClick={() => handleDelete(project.id)}>
                                                <Trash2Icon className="h-4 w-4" /> Delete
                                            </Button>
                                        </div>
                                    </TableCell>
                                )}
                            </TableRow>
                        ))}
                        {projects.data.length === 0 && (
                            <TableRow>
                                <TableCell colSpan={columns.length} className="py-4 text-center">
                                    No projects found.
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>

                {projects.links && projects.links.length > 3 && (
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
